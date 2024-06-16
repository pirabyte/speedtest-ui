#!/bin/bash

touch .env
echo APP_KEY= >> .env
php artisan key:generate

php artisan config:cache
php artisan cache:clear

# Migration der Datenbank
php artisan migrate --force

# Starten des Supervisors
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
