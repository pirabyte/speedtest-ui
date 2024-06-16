# Use PHP with Nginx and PHP-FPM
FROM php:8.2-fpm

ENV SPEEDTEST_CLI_ACCEPT_GDPR=Y

# Install system dependencies
RUN apt-get update && apt-get install -y \
    supervisor \
    cron \
    nginx \
    curl \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    autoconf \
    g++ \
    make

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql gd mbstring exif pcntl bcmath xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install Speedtest
RUN curl -s https://packagecloud.io/install/repositories/ookla/speedtest-cli/script.deb.sh | bash && \
    apt-get install -y speedtest

RUN curl -sL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
    && npm install npm@latest -g

# Setup document root
WORKDIR /var/www/html

# Copy custom nginx config
COPY ./.setup/nginx/*.conf /etc/nginx/conf.d/

# Copy supervisor config
COPY ./.setup/supervisor/*.conf /etc/supervisor/conf.d/

# Copy existing application directory contents
COPY . /var/www/html

# Copy existing application directory permissions
COPY --chown=www-data:www-data . /var/www/html

# Install all PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

RUN npm install

COPY .setup/cronjobs /etc/cron.d/laravel-scheduler
RUN chmod 0644 /etc/cron.d/laravel-scheduler
RUN crontab /etc/cron.d/laravel-scheduler

# Expose port 80 and 443
EXPOSE 80

RUN npm run build

COPY ./.setup/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Start PHP-FPM and Nginx
CMD ["/entrypoint.sh"]
