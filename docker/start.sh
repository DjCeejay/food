#!/bin/sh
set -e

cd /app/backend

# Ensure writable dirs exist
mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Cache config/routes for speed
php artisan config:clear || true
php artisan route:clear || true
php artisan config:cache
php artisan route:cache

# Run migrations (and seed) if DB is reachable
php artisan migrate --force --seed || true

# Start PHP-FPM and Reverb websocket server
php-fpm -D
php artisan reverb:start --host=0.0.0.0 --port="${REVERB_SERVER_PORT:-8080}" &

# Start Nginx in foreground
nginx -g 'daemon off;'
