#!/bin/sh
set -e

# Cache configuration, routes, and views in production
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Run storage link if not already linked
php artisan storage:link || true

# Start PHP-FPM in the background
php-fpm -D

# Start Nginx in the foreground
exec nginx -g "daemon off;"
