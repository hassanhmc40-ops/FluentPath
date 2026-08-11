# syntax=docker/dockerfile:1

############################################################
# Stage 1: build front-end assets (Vite 8 needs Node >= 20.19)
############################################################
FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json .npmrc ./

RUN npm ci --no-audit --no-fund

COPY . .

RUN npm run build

############################################################
# Stage 2: PHP-FPM runtime (Laravel 13 / PHP 8.3)
############################################################
FROM php:8.3-fpm-alpine

# PHP extensions required by this app (no Redis/Memcached: queue,
# cache and session are all DB-backed). pcntl is needed for the
# queue worker; zip is needed by Composer.
RUN apk add --no-cache libzip-dev supervisor \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql pcntl bcmath zip \
    && rm -rf /var/cache/apk/*

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Application code (vendor/node_modules/.env excluded via .dockerignore)
COPY . .

# Fresh Linux asset build from stage 1 (overwrites any host Windows build)
COPY --from=assets /app/public/build /var/www/html/public/build

RUN composer install --no-interaction --no-progress --prefer-dist \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache

# Install the custom supervisord config (php-fpm + scheduler; the queue
# worker runs as its own compose service) with nodaemon=true so PID 1 stays
# alive. Without it supervisord runs Alpine's default config, daemonizes and
# the container exits 0 -> restart loop.
COPY docker/supervisord.conf /etc/supervisord.conf

EXPOSE 9000

# Entry script migrates + seeds on first boot, then starts supervisord
# (php-fpm + scheduler). Invoked via `sh` so no exec bit needed.
CMD ["sh", "docker/php-entrypoint.sh"]