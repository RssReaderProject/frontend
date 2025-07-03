# syntax=docker/dockerfile:1

# --- Build Stage ---
FROM node:22-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# --- Composer Stage ---
FROM composer:2.8 AS composer-build
WORKDIR /app
COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader

# --- Final Stage ---
FROM php:8.4-apache

# Install system dependencies
RUN apt-get update \
    && apt-get install -y libicu-dev libxml2-dev sqlite3 libsqlite3-dev zlib1g-dev libonig-dev \
    && docker-php-ext-install intl pdo pdo_mysql pdo_sqlite mbstring xml

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set working directory
WORKDIR /var/www

# Set production environment variables
ENV APP_ENV=production
ENV APP_DEBUG=false

# Copy the entire Laravel application
COPY . .

# Copy built assets and vendor from previous stages
COPY --from=node-build /app/public/build/ ./public/build/
COPY --from=node-build /app/node_modules/ ./node_modules/
COPY --from=node-build /app/vite.config.ts ./
COPY --from=composer-build /app/vendor/ ./vendor/

# Clear Laravel cache to ensure dont-discover configuration takes effect
RUN rm -rf bootstrap/cache/*.php

# Ensure database directory exists for SQLite
RUN mkdir -p database && touch database/database.sqlite

# Set correct permissions for Laravel directories
RUN chown -R www-data:www-data storage bootstrap/cache database vendor \
    && chmod -R 775 storage bootstrap/cache database vendor

# Create basic .env file for production
RUN echo "APP_NAME=Laravel" > .env \
    && echo "APP_ENV=production" >> .env \
    && echo "APP_KEY=base64:$(openssl rand -base64 32)" >> .env \
    && echo "APP_DEBUG=false" >> .env \
    && echo "APP_URL=http://localhost" >> .env \
    && echo "LOG_CHANNEL=stack" >> .env \
    && echo "LOG_DEPRECATIONS_CHANNEL=null" >> .env \
    && echo "LOG_LEVEL=debug" >> .env \
    && echo "DB_CONNECTION=sqlite" >> .env \
    && echo "DB_DATABASE=/var/www/database/database.sqlite" >> .env \
    && echo "BROADCAST_DRIVER=log" >> .env \
    && echo "CACHE_DRIVER=file" >> .env \
    && echo "FILESYSTEM_DISK=local" >> .env \
    && echo "QUEUE_CONNECTION=sync" >> .env \
    && echo "SESSION_DRIVER=file" >> .env \
    && echo "SESSION_LIFETIME=120" >> .env \
    && echo "RSS_SERVICE_URL=http://localhost:8080" >> .env \
    && echo "RSS_ITEM_RETENTION_DAYS=30" >> .env

# Configure Apache to use Laravel's public directory as document root
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/public|g' /etc/apache2/sites-available/000-default.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/apache2.conf

# Expose Apache port
EXPOSE 80

# Use Apache default CMD 