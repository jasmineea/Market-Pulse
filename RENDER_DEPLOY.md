# Deploying to Render – Login/Signup Checklist

If the homepage loads but **login and signup don’t work**, set these in your Render service **Environment** tab and redeploy.

## Required environment variables

| Variable | Value | Why |
|----------|--------|-----|
| `APP_URL` | `https://YOUR-SERVICE-NAME.onrender.com` | Must match your Render URL so redirects and links work. |
| `APP_ENV` | `production` | Recommended for production. |
| `APP_DEBUG` | `false` | Don’t expose errors in production. |
| `SESSION_SECURE_COOKIE` | `true` | Required over HTTPS so the session cookie is sent. |
| `SESSION_DOMAIN` | Leave **empty** or unset | Lets the cookie work on your Render domain. |

### Using a custom domain (e.g. www.terpinsights.com)

If you point a **custom domain** (e.g. `www.terpinsights.com`) to your Render service and see **419 Page Expired** on login:

1. **Set `APP_URL`** to your canonical URL: `https://www.terpinsights.com` (or `https://terpinsights.com` if you use non-www). This must match the URL users see so redirects and CSRF work.
2. **Set `SESSION_DOMAIN`** to your root domain with a leading dot: `.terpinsights.com`. That lets the session cookie work for both `www.terpinsights.com` and `terpinsights.com`.
3. **Keep `SESSION_SECURE_COOKIE`** set to `true` (HTTPS).
4. **Redeploy** after changing these so the new config is used.

## Database (you’re using Supabase/Postgres)

- Copy your **production** DB vars from Supabase into Render: `DB_CONNECTION`, `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`.
- Migrations run on each deploy via `start.sh`, so `users` and `sessions` tables will exist.

## Adding BigQuery credentials on Render

You can't upload a file on Render, so use **inline JSON** in an environment variable.

1. **Get your service account JSON key**  
   Google Cloud Console → IAM & Admin → Service Accounts → your BigQuery service account → Keys → Add key → JSON. Download the file.

2. **Make it a single line** (no line breaks) so it can go in an env var:
   - **Terminal (recommended):** `cat path/to/your-key.json | jq -c .` then copy the full output. Do not manually replace newlines with spaces or the private_key will break.
   - **Editor:** Open the JSON, copy all, then find-and-replace newlines with a space so it’s one line, or
   - **Online:** Paste at [jsonformatter.org/json-minify](https://jsonformatter.org/json-minify) and copy the minified result.

3. **In Render:** Dashboard → your service → **Environment**:
   - **Do not** put the JSON in `GOOGLE_APPLICATION_CREDENTIALS` — that variable must be a **file path**, not the JSON content. On Render you have no file path, so leave it **unset** (or delete it).
   - **Add** (or use) **Key:** `GOOGLE_APPLICATION_CREDENTIALS_JSON` and **Value:** paste the **entire** one-line JSON (starts with `{"type":"service_account",...`). The app reads this variable and writes the JSON to a temp file for BigQuery.

4. **Set** `BQ_PROJECT_ID` to your BigQuery project ID (e.g. `mca-dashboard-456223`). If you omit it, the app will use the `project_id` from the JSON (your service account key includes it).

5. **Redeploy** the service so the new env var is used.

The app writes the JSON to a temp file at runtime and uses it for BigQuery. Do **not** set `GOOGLE_APPLICATION_CREDENTIALS` to a local path on Render; leave it unset and use `GOOGLE_APPLICATION_CREDENTIALS_JSON` instead.

**If you see "private_key is invalid or not defined":** The JSON in the env var is usually truncated or the `private_key` was broken when minifying. Fix by: (1) Regenerating the one-line JSON with `jq -c . your-key.json` and pasting the **full** output into Render, or (2) Using a proper JSON minifier (not "replace all newlines with space") so the `private_key` value stays valid.

## After changing env vars

Redeploy the service so the new values are picked up. If you only change env vars and don’t redeploy, the old values are still in use.

## Code changes in this repo

- **Trust proxies** – `bootstrap/app.php` trusts proxies so Laravel sees HTTPS and the correct host (needed for cookies and redirects).
- **Migrations on deploy** – `start.sh` runs `php artisan migrate --force` so the DB schema is up to date.
- **BigQuery optional** – `BigQueryService` and `MarketPulseMetrics` no longer require a credentials file at boot. If credentials are missing, the app still runs; dashboard and Market Pulse show empty data. On Render you can add BigQuery via the `GOOGLE_APPLICATION_CREDENTIALS_JSON` env var (see “Adding BigQuery credentials on Render” above).
