# Swoole 6.2.1 + Redis 6.3.0 + PHP 8.3.31 ВЖЕ скомпільовані в офіційному образі
# phpswoole — це прибирає ~10-15хв `pecl install swoole` з КОЖНОГО деплою
# (білд падає з ~20хв до ~6-9хв). Версії звірено runtime-екстракцією і вони
# ідентичні попередньому проду: PHP 8.3.31, Swoole 6.2.1 (http2 + OpenSSL 3.5.7),
# redis, pdo_mysql, mbstring, OPcache — усе present у базі.
# Pin по digest (php8.3-alpine — moving tag) для відтворюваності. Bump digest
# СВІДОМО лише під PHP/Swoole security-патч (див. docs нижче).
FROM phpswoole/swoole@sha256:b12a5f4f73a6523a08eefa164bc0ee18a3d16a8569a0547c95c4682a92540bc8

# Системні пакети + nginx/supervisor + nodejs/npm (Vite). dev-libs для ext,
# які доставляємо нижче.
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    zip \
    unzip \
    git \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    libzip-dev \
    oniguruma-dev \
    icu-dev \
    mysql-client \
    nodejs \
    npm

# Доставляємо ЛИШЕ ті ext, яких НЕМАЄ в базі (swoole/redis/opcache/pdo_mysql/
# mbstring уже є). Це bundled-PHP розширення — компілюються за ~110с (а не
# Swoole-pecl на 10-15хв). exif перевірено: у базі ВІДСУТНІЙ → ставимо.
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
    gd \
    intl \
    zip \
    bcmath \
    pcntl \
    exif

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files first for better layer caching
COPY composer.json composer.lock ./

# composer.json has classmap: ["modules/"] for plugin-style modular system
# (see modules/README.md). The dir must exist at install time even if empty.
RUN mkdir -p modules

# Install dependencies (without scripts since app files aren't copied yet)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy ALL application files
COPY . .

# Create storage + bootstrap/cache directories BEFORE composer post-autoload
# (artisan package:discover writes its manifest into bootstrap/cache, will
# fail with "directory must be present" otherwise — these paths are .gitignored
# and don't exist in the cloned source).
RUN mkdir -p storage/logs \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/public \
    bootstrap/cache \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache

# Regenerate classmap — modules/ classmap was scanned in earlier step when
# the dir was empty (placeholder). Now that real modules/* files are present,
# we need a fresh classmap so module classes (App\Models\NpShipment, etc.) resolve.
RUN composer dump-autoload --no-dev --optimize --no-scripts

# Run post-install scripts (package:discover, etc.) and publish Filament assets
RUN composer run-script post-autoload-dump --no-interaction \
    && php artisan filament:assets --ansi

# Build Vite assets (creates public/build/manifest.json that @vite() needs)
RUN npm ci --no-audit --no-fund \
    && npm run build \
    && rm -rf node_modules

# Copy nginx and supervisor configs
RUN mkdir -p /run/nginx
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/supervisord.conf /etc/supervisord.conf

# Production OPcache + memory_limit + realpath cache.
# validate_timestamps=0 means PHP never re-stats files — biggest single
# perf win for Laravel under prod traffic (3s cold → ~150ms warm).
COPY docker/php-opcache.ini /usr/local/etc/php/conf.d/zz-opcache.ini

# Copy entrypoint script
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 80

ENTRYPOINT ["docker-entrypoint.sh"]
