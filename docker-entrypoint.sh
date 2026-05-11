#!/bin/sh
set -e

echo "[entrypoint] Starting SimpleShop container..."

# Ensure storage directories exist with correct permissions
mkdir -p /var/www/html/storage/logs \
         /var/www/html/storage/framework/cache/data \
         /var/www/html/storage/framework/sessions \
         /var/www/html/storage/framework/views \
         /var/www/html/storage/app/public \
         /var/www/html/bootstrap/cache

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear stale compiled views (perms or content may be stale after bind-mount swap)
rm -f /var/www/html/storage/framework/views/*.php

# Create storage symlink (force-recreate if broken or pointing to old path)
if [ ! -e /var/www/html/public/storage ] || [ ! -d /var/www/html/public/storage/ ]; then
    rm -f /var/www/html/public/storage
    php artisan storage:link --force 2>/dev/null || true
fi

# Publish Filament assets (re-publish on every start — bind-mounted public/ may not have them)
echo "[entrypoint] Publishing Filament assets..."
php artisan filament:assets --ansi 2>&1 || echo "[entrypoint] WARNING: filament:assets failed, continuing..."

# Run migrations
echo "[entrypoint] Running migrations..."
php artisan migrate --force 2>&1 || echo "[entrypoint] WARNING: Migrations failed, continuing..."

# Auto-seed demo catalog on FIRST deploy only (when products table is empty).
# Triggered by MODULE_AUTO_PARTS_SEED=true. Idempotent: skips if rows exist.
if [ "$MODULE_AUTO_PARTS_SEED" = "true" ]; then
    # Marker file lives on storage volume so it persists across container
    # restarts but stays absent across image rebuilds with fresh volumes.
    SEED_MARKER=/var/www/html/storage/app/.auto-parts-seeded
    if [ ! -f "$SEED_MARKER" ]; then
        echo "[entrypoint] Marker absent — running AutoPartsSeeder..."
        if php artisan db:seed --class=AutoPartsSeeder --force 2>&1; then
            touch "$SEED_MARKER"
            echo "[entrypoint] AutoPartsSeeder finished. Marker written."
        else
            echo "[entrypoint] WARNING: AutoPartsSeeder failed, continuing..."
        fi
    else
        echo "[entrypoint] Auto-seed marker present — skipping."
    fi
fi

# Debug: show effective env (first chars of secrets, full values of toggles)
echo "[entrypoint] ENV check:"
echo "  APP_ENV=$APP_ENV  APP_DEBUG=$APP_DEBUG  APP_URL=$APP_URL"
echo "  DB_HOST=$DB_HOST  DB_DATABASE=$DB_DATABASE  DB_USERNAME=$DB_USERNAME"
echo "  REDIS_HOST=$REDIS_HOST  REDIS_PORT=$REDIS_PORT"
echo "  CACHE_DRIVER=$CACHE_DRIVER  SESSION_DRIVER=$SESSION_DRIVER  QUEUE_CONNECTION=$QUEUE_CONNECTION"
echo "  TRUSTED_PROXIES=$TRUSTED_PROXIES"

# Cache configuration — clear first so re-reads .env, then cache
echo "[entrypoint] Caching configuration..."
php artisan config:clear || true
php artisan view:clear || true
php artisan route:clear || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Setup Meilisearch if configured
if [ "$SCOUT_DRIVER" = "meilisearch" ]; then
    echo "[entrypoint] Setting up Meilisearch indexes..."
    php artisan search:setup 2>&1 || echo "[entrypoint] WARNING: search:setup failed, continuing..."
fi

echo "[entrypoint] Starting supervisord..."

# Background tail of laravel.log so Coolify "logs" tab streams exceptions
# even when APP_DEBUG=false hides them from the user-facing page.
( while true; do
    if [ -s /var/www/html/storage/logs/laravel.log ]; then
      tail -F /var/www/html/storage/logs/laravel.log 2>/dev/null | sed 's/^/[laravel] /'
      break
    fi
    sleep 2
  done
) &

exec /usr/bin/supervisord -c /etc/supervisord.conf
