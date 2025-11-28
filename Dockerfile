# syntax=docker/dockerfile:1
FROM php:8.3-cli-bullseye

ARG DEBIAN_FRONTEND=noninteractive

# System deps + pgsql + gd + zip
RUN apt-get update \
 && apt-get install -y --no-install-recommends \
    git unzip zip curl libpq-dev libzip-dev \
    libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
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

# Copy app code
COPY backend /app/backend

# Env for build
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV APP_ENV=production
ENV APP_KEY=base64:placeholderbuildkey000000000000000000000000000000000000000

# Install PHP deps
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Install Node deps
RUN npm ci --no-progress

# Build assets and cache config/routes; ensure storage link
RUN npm run build \
 && php -r "if (is_link('public/storage')) { unlink('public/storage'); }" \
 && php artisan storage:link \
 && php artisan config:cache \
 && php artisan route:cache

ENV PORT=8000
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
