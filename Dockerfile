FROM composer:2 AS builder

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader \
    --no-scripts

COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM php:8.2-fpm-alpine

RUN apk add --no-cache \
    nginx \
    bash \
    icu-dev \
    libzip-dev \
    postgresql-dev \
    oniguruma-dev \
    curl \
    && docker-php-ext-install pdo pdo_pgsql intl zip opcache

WORKDIR /var/www/html

COPY --from=builder /app /var/www/html
COPY docker/nginx.conf /etc/nginx/http.d/default.conf
COPY docker/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /entrypoint.sh \
    && mkdir -p /run/nginx /var/cache/nginx /var/log/nginx \
    && mkdir -p /var/www/html/var/cache /var/www/html/var/log \
    && chown -R www-data:www-data /var/www/html/var

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
