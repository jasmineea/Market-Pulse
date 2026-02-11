#!/usr/bin/env bash
set -e

PORT="${PORT:-10000}"

# Clear old caches (important when env vars change)
php artisan config:clear || true
php artisan route:clear  || true
php artisan view:clear   || true
php artisan cache:clear  || true

# Optional: cache for prod speed (safe)
php artisan config:cache || true
php artisan route:cache  || true
php artisan view:cache   || true

# Optional: storage symlink
php artisan storage:link || true

# Run migrations (required for users/sessions tables; safe to run on every deploy)
php artisan migrate --force || true

exec php artisan serve --host=0.0.0.0 --port="${PORT}"
