FROM php:8.3-cli-alpine

# Laravel Octane (Swoole) replaces php-fpm. Nginx залишається як reverse-proxy
# на :80 + статика, Octane worker слухає 127.0.0.1:8000.
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
    mysql-client

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    intl \
    opcache

# Install Redis + Swoole (Octane). Build deps installed once, both extensions
# compiled, deps removed to keep image small.
RUN apk add --no-cache --virtual .build-deps autoconf g++ make linux-headers openssl-dev curl-dev \
    && pecl install redis \
    && pecl install swoole \
    && docker-php-ext-enable redis swoole \
    && apk del .build-deps

# Install Node.js + npm for Vite frontend build
RUN apk add --no-cache nodejs npm

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
