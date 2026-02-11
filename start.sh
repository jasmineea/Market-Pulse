#!/usr/bin/env bash
set -e

PORT="${PORT:-10000}"

# Clear config/route/view caches so env and code changes take effect (do NOT clear app cache — BigQuery cache should persist for speed)
php artisan config:clear || true
php artisan route:clear  || true
php artisan view:clear   || true

# Cache config/route/views for prod speed
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true

# Optional: storage symlink
php artisan storage:link || true

# Run migrations (required for users/sessions tables; safe to run on every deploy)
php artisan migrate --force || true

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
