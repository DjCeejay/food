# syntax=docker/dockerfile:1
FROM php:8.3-cli-bullseye

ARG DEBIAN_FRONTEND=noninteractive

# System deps + pgsql + gd + zip + mbstring deps
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git unzip zip curl libpq-dev libzip-dev libonig-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_pgsql gd zip bcmath mbstring \
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
ENV APP_KEY=base64:zI8Ry0Ry9oZ01Iw3ZsUBSocwMMwicmjp/IuFvHtvsKo=
ENV APP_URL=https://afc.com.ng
ENV REVERB_APP_ID=placeholder-id
ENV REVERB_APP_KEY=placeholder-key
ENV REVERB_APP_SECRET=placeholder-secret
ENV REVERB_HOST=localhost
ENV REVERB_PORT=443
ENV REVERB_SCHEME=https

# Install PHP deps
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Install Node deps
RUN npm ci --no-progress

# Build assets and cache config/routes; ensure storage link
RUN npm run build \
 && php -r "if (is_link('public/storage')) { unlink('public/storage'); }" \
 && php artisan storage:link

ENV PORT=8000
EXPOSE 8000 8080
CMD ["sh", "-c", "mkdir -p storage/framework/{cache,data,sessions,views} bootstrap/cache resources/views && chmod -R 777 storage bootstrap/cache resources && php artisan config:clear && php artisan route:clear && php artisan config:cache && php artisan route:cache && php artisan migrate --force --seed && php artisan reverb:start --host=0.0.0.0 --port=${REVERB_SERVER_PORT:-8080} & php artisan serve --host=0.0.0.0 --port=${PORT:-8000}"]
