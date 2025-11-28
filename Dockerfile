# syntax=docker/dockerfile:1

# Build a PHP 8.3 container with Node for Vite assets
FROM php:8.3-cli-bullseye

ARG DEBIAN_FRONTEND=noninteractive

# System deps + PostgreSQL driver + tools
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip zip curl libpq-dev libzip-dev \
        libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql gd zip \
    && rm -rf /var/lib/apt/lists/*

# Install Node 20.x
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get update \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /app

# Install PHP deps first (faster layer caching)
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --prefer-dist --no-progress --no-interaction

# Install Node deps
COPY backend/package.json backend/package-lock.json ./
RUN npm ci --no-progress

# Copy application code
COPY backend .

# Build assets and cache config/routes; tolerate existing storage link
RUN npm run build \
    && php -r "if (is_link('public/storage')) { unlink('public/storage'); }" \
    && php artisan storage:link \
    && php artisan config:cache \
    && php artisan route:cache

ENV PORT=8000
EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
