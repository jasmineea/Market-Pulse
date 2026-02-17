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

## Production speed (slow first load)

The app caches BigQuery results for 30 minutes so dashboard and Market Pulse stay fast. Two things can still make production feel slow:

1. **First request after deploy** – Cache is empty, so the first page load runs several BigQuery queries (5+ on dashboard, more on Market Pulse). Later requests use the cache and are fast.
2. **Cold starts** – On Render, if the service spins down after inactivity, the first request after idle wakes it (often 30–60+ seconds), then that request also hits BigQuery with an empty cache.

**What we do in code**

- `start.sh` no longer runs `php artisan cache:clear`, so BigQuery cache is **not** wiped on every deploy/start. Config/route/view caches are still cleared and rebuilt so env changes take effect.
- BigQuery cache TTL is 30 minutes (longer for dashboard-only keys) so once cache is warm, pages stay fast longer.

**Optional: use database cache so cache survives restarts**

- Set **`CACHE_STORE`** to **`database`** in Render Environment. The app already includes a `cache` table migration (from `php artisan cache:table`); migrations run on deploy, so the table will exist. BigQuery result cache will then be stored in your Postgres DB and persist across deploys and restarts, reducing cold loads.

**Optional: sync BigQuery to app DB for fastest dashboard/Market Pulse**

- BigQuery remains the **source of truth**. The app can sync BQ data into the same database as your users so dashboard and Market Pulse read from local DB (no BQ on request). Run the sync on a schedule (e.g. daily):
  - **Artisan:** `php artisan market-pulse:sync-bigquery` (run from cron or Render cron job).
  - **Laravel scheduler:** The app schedules this command daily in `routes/console.php`. Ensure the scheduler is running (e.g. one worker process or external cron calling `php artisan schedule:run` every minute).
- After the first successful sync, dashboard and Market Pulse will use local data. Optional: show “Data as of &lt;last_synced_at&gt;” in the UI via `MarketPulseDataService::getLastSyncedAt()`.

**Optional: warm the cache after deploy**

1. In Render **Environment**, add: **Key** `CACHE_WARMUP_SECRET`, **Value** a long random string (keep it private).
2. After each deploy, call: `GET https://YOUR-SERVICE.onrender.com/warmup?secret=YOUR_CACHE_WARMUP_SECRET` (e.g. from a cron or deploy hook). If the secret is not set, `/warmup` returns 404.

**Optional: reduce cold starts**

Use a free uptime monitor (e.g. UptimeRobot) to ping your site every 5–10 minutes so the service stays warm.

## Code changes in this repo

- **Trust proxies** – `bootstrap/app.php` trusts proxies so Laravel sees HTTPS and the correct host (needed for cookies and redirects).
- **Migrations on deploy** – `start.sh` runs `php artisan migrate --force` so the DB schema is up to date.
- **BigQuery optional** – `BigQueryService` and `MarketPulseMetrics` no longer require a credentials file at boot. If credentials are missing, the app still runs; dashboard and Market Pulse show empty data. On Render you can add BigQuery via the `GOOGLE_APPLICATION_CREDENTIALS_JSON` env var (see “Adding BigQuery credentials on Render” above).
