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
    && docker-php-ext-install pdo_pgsql bcmath opcache \
    && a2enmod rewrite headers deflate expires \
    && rm -rf /var/lib/apt/lists/*

# Bigger uploads (images / videos up to 20 MB)
RUN { \
      echo 'upload_max_filesize=25M'; \
      echo 'post_max_size=30M'; \
      echo 'memory_limit=256M'; \
    } > /usr/local/etc/php/conf.d/uploads.ini

# Speed: keep the compiled PHP code in memory (the code never changes after the build)
RUN { \
      echo 'opcache.enable=1'; \
      echo 'opcache.memory_consumption=128'; \
      echo 'opcache.max_accelerated_files=20000'; \
      echo 'opcache.validate_timestamps=0'; \
      echo 'realpath_cache_size=4096K'; \
      echo 'realpath_cache_ttl=600'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# Apache serves the "public" folder, on the port Render gives us ($PORT)
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
ENV PORT=10000
RUN sed -ri 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -i 's/Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/:80>/:${PORT}>/' /etc/apache2/sites-available/000-default.conf

# Speed: browsers keep the built CSS/JS (their names change on every build), and text is compressed
RUN { \
      echo '<Directory /var/www/html/public/build>'; \
      echo '    Header set Cache-Control "public, max-age=31536000, immutable"'; \
      echo '</Directory>'; \
      echo '<Directory /var/www/html/public/images>'; \
      echo '    Header set Cache-Control "public, max-age=604800"'; \
      echo '</Directory>'; \
      echo 'AddOutputFilterByType DEFLATE text/html text/css application/javascript application/json image/svg+xml'; \
    } > /etc/apache2/conf-available/speed.conf \
    && a2enconf speed

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
