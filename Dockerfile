# syntax=docker/dockerfile:1
FROM php:8.3-fpm-bullseye

ARG DEBIAN_FRONTEND=noninteractive

# System deps + pgsql + gd + zip + nginx
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git unzip zip curl libpq-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    nginx supervisor \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_pgsql gd zip \
 && rm -rf /var/lib/apt/lists/*

# Node 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
 && apt-get update && apt-get install -y --no-install-recommends nodejs \
 && rm -rf /var/lib/apt/lists/*

# Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app/backend

# Copy app code (including views)
COPY backend /app/backend

# Ensure storage/bootstrap cache directories exist and are writable
RUN mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache \
 && chmod -R 777 storage bootstrap/cache

# Env for build
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_BASE_PATH=/app/backend
ENV APP_ENV=production
ENV APP_KEY=base64:placeholderbuildkey000000000000000000000000000000000000000
ENV APP_URL=https://afc.com.ng

# Install PHP deps
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Install Node deps
RUN npm ci --no-progress

# Build assets and cache config/routes; ensure storage link
RUN npm run build \
 && php -r "if (is_link('public/storage')) { unlink('public/storage'); }" \
 && php artisan storage:link

# Configure Nginx
RUN rm /etc/nginx/sites-enabled/default && rm -f /etc/nginx/sites-available/default
COPY <<EOF /etc/nginx/sites-available/default
server {
    listen 8000;
    server_name _;
    root /app/backend/public;
    index index.php;

    client_max_body_size 20M;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* \.(jpg|jpeg|png|gif|ico|css|js|svg|woff|woff2|ttf|eot)$ {
        expires 30d;
        add_header Cache-Control "public, immutable";
    }

    location ~ /\.ht {
        deny all;
    }
}
EOF

RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

# Configure supervisor
COPY <<EOF /etc/supervisor/conf.d/laravel.conf
[program:php-fpm]
command=php-fpm
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/php-fpm.log

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
redirect_stderr=true
stdout_logfile=/var/log/nginx.log
EOF

ENV PORT=8000
EXPOSE 8000
CMD ["sh", "-c", "mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache resources/views && chmod -R 777 storage bootstrap/cache resources && php artisan config:clear && php artisan route:clear && php artisan config:cache && php artisan route:cache && php artisan migrate --force --seed && supervisord -c /etc/supervisor/supervisord.conf"]
