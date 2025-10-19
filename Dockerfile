FROM composer:2.8 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-ansi --no-interaction --no-progress --prefer-dist --no-scripts

FROM php:8.2-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache sqlite sqlite-dev \
    && docker-php-ext-install pdo pdo_sqlite

COPY --from=vendor /app/vendor ./vendor
COPY . .

RUN chmod +x docker/entrypoint.sh \
    && chown -R www-data:www-data storage bootstrap/cache database

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]
CMD ["php-fpm"]
