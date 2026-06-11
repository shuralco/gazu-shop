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
# Triggered by MODULE_AUTO_PARTS_SEED=true. Runs ONLY when the products table
# is genuinely empty — we check the DB directly, not a marker file. The
# storage/ marker did NOT survive image rebuilds, so the old marker-based
# check re-ran ChineseAutoPartsSeeder on EVERY deploy — and that seeder calls
# truncateDemo(), which wipes the whole catalog (+ any manually-created data)
# and re-creates it with fresh ids. DB-count is the only robust signal.
if [ "$MODULE_AUTO_PARTS_SEED" = "true" ]; then
    PRODUCT_COUNT=$(php artisan tinker --execute='echo \App\Models\Product::count();' 2>/dev/null | tr -cd '0-9')
    if [ "$PRODUCT_COUNT" = "0" ]; then
        echo "[entrypoint] Products table empty — running ChineseAutoPartsSeeder..."
        if SEED_FORCE=1 php artisan db:seed --class=ChineseAutoPartsSeeder --force 2>&1; then
            echo "[entrypoint] ChineseAutoPartsSeeder finished."
        else
            echo "[entrypoint] WARNING: ChineseAutoPartsSeeder failed, continuing..."
        fi
    elif [ -z "$PRODUCT_COUNT" ]; then
        echo "[entrypoint] WARNING: could not read product count — skipping seed (safe default)."
    else
        echo "[entrypoint] Catalog already has $PRODUCT_COUNT products — skipping seed."
    fi
fi

# Bootstrap admin user + default warehouse (idempotent — re-runnable safely).
ADMIN_MARKER=/var/www/html/storage/app/.admin-bootstrapped
if [ ! -f "$ADMIN_MARKER" ]; then
    echo "[entrypoint] Bootstrapping admin user + default warehouse..."
    if php artisan gazu:bootstrap-admin 2>&1; then
        touch "$ADMIN_MARKER"
        echo "[entrypoint] Admin bootstrap done. Login at /admin/login with admin@gazu.com / changeme123 — CHANGE PASSWORD IMMEDIATELY."
    else
        echo "[entrypoint] WARNING: admin bootstrap failed, continuing..."
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

# CRITICAL: clear ResponseCache so cached HTML doesn't reference stale Vite
# asset hashes from the previous deploy (HTML refs gazu-ABC.css but new
# build produced gazu-XYZ.css → 404 on every asset → broken styles).
php artisan responsecache:clear || true
php artisan cache:clear || true

# Setup Meilisearch if configured
if [ "$SCOUT_DRIVER" = "meilisearch" ]; then
    echo "[entrypoint] Setting up Meilisearch indexes..."
    php artisan search:setup 2>&1 || echo "[entrypoint] WARNING: search:setup failed, continuing..."
fi

# Авто-прогрів ResponseCache: вище ми його очистили (щоб не віддавати HTML зі
# старими asset-хешами), тож перший хіт кожної сторінки — холодний рендер (~1с
# проти ~0.18с з кешу). У фоні чекаємо, поки Octane почне відповідати, і
# обходимо ключові сторінки (sitemap) проти APP_URL — перший реальний
# відвідувач отримує вже теплий кеш. Вимикається WARM_CACHE_AFTER_DEPLOY=false.
if [ "$WARM_CACHE_AFTER_DEPLOY" != "false" ]; then
  (
    for i in $(seq 1 80); do
      if curl -fsS -o /dev/null --max-time 5 "http://127.0.0.1:80/" 2>/dev/null; then
        echo "[entrypoint] Octane відповідає — прогрів ResponseCache..."
        # --products: прогріваємо й сторінки товарів (1000+) — клієнти заходять
        # на них з Google, холодний хіт ~1с. Послідовно, у фоні — навантаження низьке.
        php artisan cache:warm --products 2>&1 | sed 's/^/[warm] /'
        break
      fi
      sleep 3
    done
  ) &
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

# Якщо контейнеру передано CMD (queue: "php artisan queue:work...",
# scheduler: "php artisan schedule:work") — виконуємо ЙОГО, а не supervisord.
# Раніше entrypoint завжди стартував supervisord (nginx+fpm) → queue-воркер
# НІКОЛИ не працював, queued-листи (замовлення/ТТН) вічно висіли в Redis.
if [ "$#" -gt 0 ]; then
    echo "[entrypoint] exec CMD: $*"
    exec "$@"
fi

exec /usr/bin/supervisord -c /etc/supervisord.conf
