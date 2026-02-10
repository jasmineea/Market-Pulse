#!/usr/bin/env bash
set -e

# Render provides PORT. Default fallback just in case.
PORT="${PORT:-10000}"

# If APP_KEY is missing, Laravel will 500. Render should set this as an env var.
# (Do NOT generate a new key on every boot in production.)
php -v

# Cache/optimize (safe on boot)
php artisan config:clear || true
php artisan route:clear || true
php artisan view:clear || true

# Only run these if you want caching in prod:
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Storage symlink (optional)
php artisan storage:link || true

# Start Laravel (listens on all interfaces for Render)
exec php artisan serve --host=0.0.0.0 --port="${PORT}"
