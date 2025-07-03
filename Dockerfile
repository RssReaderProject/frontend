# syntax=docker/dockerfile:1

# --- Build Stage ---
FROM node:22-alpine AS node-build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources/ ./resources/
COPY vite.config.ts ./
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
WORKDIR /var/www/html

# Copy built assets and vendor
COPY --from=node-build /app/resources/ ./resources/
COPY --from=node-build /app/node_modules/ ./node_modules/
COPY --from=node-build /app/vite.config.ts ./
COPY --from=composer-build /app/vendor/ ./vendor/

# Copy rest of the application
COPY . .

# Copy Laravel public files to Apache web root
COPY public/ /var/www/html/

# Ensure database directory exists for SQLite
RUN mkdir -p database && touch database/database.sqlite

# Set correct permissions for Laravel
RUN chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R 775 storage bootstrap/cache database

# Configure Apache to allow .htaccess overrides
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Expose Apache port
EXPOSE 80

# Use Apache default CMD 