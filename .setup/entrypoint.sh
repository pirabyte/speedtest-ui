#!/bin/bash
set -e

cd /var/www/html

# Create .env from example if not present
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate APP_KEY if not already set
if [ -z "$APP_KEY" ] && ! grep -q "^APP_KEY=base64:" .env; then
    php artisan key:generate --force
fi

# Create SQLite database file if using SQLite
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    DB_PATH="${DB_DATABASE:-database/database.sqlite}"
    mkdir -p "$(dirname "$DB_PATH")"
    touch "$DB_PATH"
    chown www-data:www-data "$DB_PATH"
fi

# Ensure storage and cache directories are writable
chown -R www-data:www-data storage bootstrap/cache

php artisan config:cache
php artisan migrate --force

exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
