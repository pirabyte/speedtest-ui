# syntax=docker/dockerfile:1.7

FROM php:8.4-fpm AS base

ENV SPEEDTEST_CLI_ACCEPT_GDPR=Y
ENV LOG_CHANNEL=stderr

# Install system dependencies required at runtime and for PHP extension builds.
RUN apt-get update && apt-get install -y \
    supervisor \
    cron \
    nginx \
    curl \
    gnupg \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libsqlite3-dev \
    zip \
    autoconf \
    g++ \
    make \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions once in the shared base image so app code changes do
# not invalidate the slowest build layers.
RUN docker-php-ext-install pdo_mysql pdo_sqlite gd mbstring exif pcntl bcmath xml

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Speedtest
RUN curl -s https://packagecloud.io/install/repositories/ookla/speedtest-cli/script.deb.sh | bash && \
    apt-get install -y speedtest && \
    rm -rf /var/lib/apt/lists/*

FROM base AS composer-deps

WORKDIR /var/www/html

COPY composer.json composer.lock ./

RUN --mount=type=cache,target=/tmp/composer-cache \
    COMPOSER_CACHE_DIR=/tmp/composer-cache \
    composer install \
    --no-interaction \
    --prefer-dist \
    --no-dev \
    --no-scripts

FROM oven/bun:1 AS frontend

WORKDIR /app

COPY package.json package-lock.json bun.lock ./

RUN --mount=type=cache,target=/root/.bun/install/cache \
    bun install --frozen-lockfile

COPY vite.config.js postcss.config.js tailwind.config.js ./
COPY resources ./resources
COPY --from=composer-deps /var/www/html/vendor ./vendor

RUN bun run build

FROM base AS runtime

WORKDIR /var/www/html

# Copy custom nginx config
COPY ./.setup/nginx/*.conf /etc/nginx/conf.d/

# Copy supervisor config
COPY ./.setup/supervisor/*.conf /etc/supervisor/conf.d/

# Copy application source after dependency stages so source-only changes do not
# invalidate Composer or Bun install layers.
COPY --chown=www-data:www-data . /var/www/html
COPY --chown=www-data:www-data ./database/migrations /opt/laravel-migrations
COPY --from=composer-deps --chown=www-data:www-data /var/www/html/vendor /var/www/html/vendor
COPY --from=frontend --chown=www-data:www-data /app/public/build /var/www/html/public/build

RUN composer dump-autoload --no-interaction --optimize --no-dev && \
    php artisan package:discover --ansi

COPY .setup/cronjobs /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler
RUN crontab /etc/cron.d/laravel-scheduler

# Expose port 80
EXPOSE 80

COPY ./.setup/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Health check using Laravel's built-in /up endpoint
HEALTHCHECK --interval=30s --timeout=5s --start-period=60s --retries=3 \
    CMD curl -f http://localhost/up || exit 1

# Start PHP-FPM and Nginx
CMD ["/entrypoint.sh"]
