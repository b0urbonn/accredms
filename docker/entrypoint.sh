#!/bin/sh
set -e

# Dynamically set Nginx listening port (Render provides $PORT, defaults to 80)
PORT="${PORT:-80}"
sed -i "s/listen 80;/listen ${PORT};/g" /etc/nginx/http.d/default.conf

# Ensure writable storage permissions at runtime
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Run storage link if not already linked
php artisan storage:link || true

# Cache config & routes
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

# Start PHP-FPM in the background
php-fpm -D

# Start Nginx in the foreground
echo "Server starting on port ${PORT}..."
exec nginx -g "daemon off;"
