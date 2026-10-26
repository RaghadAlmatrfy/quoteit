# ------------------------------------------------------------
# Tuwaiq Quotes (QuoteIt) — image for Render (or any Docker host)
# ------------------------------------------------------------

# 1) PHP packages (needed by Tailwind to read the pagination views)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

# 2) Build the CSS and JavaScript with Vite
FROM node:22-slim AS assets
WORKDIR /app
COPY package.json package-lock.json .npmrc ./
RUN npm ci
COPY . .
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# 3) The web server: PHP 8.4 + Apache
FROM php:8.4-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev libzip-dev unzip \
    && docker-php-ext-install pdo_pgsql bcmath \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Bigger uploads (images / videos up to 20 MB)
RUN { \
      echo 'upload_max_filesize=25M'; \
      echo 'post_max_size=30M'; \
      echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# Apache serves the "public" folder, on the port Render gives us ($PORT)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=10000
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-dev --no-interaction \
    && mkdir -p public/uploads storage/framework/cache storage/framework/sessions storage/framework/views storage/logs \
    && chown -R www-data:www-data storage bootstrap/cache public/uploads \
    && chmod +x docker/start.sh

EXPOSE 10000
CMD ["docker/start.sh"]
