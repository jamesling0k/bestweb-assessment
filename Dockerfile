FROM composer:2 AS composer

FROM php:8.3-cli-alpine

RUN apk add --no-cache freetype libjpeg-turbo libpng libzip \
    && apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS freetype-dev libjpeg-turbo-dev libpng-dev libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" bcmath gd pcntl pdo_mysql zip \
    && apk del .build-deps

COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY docker/entrypoint.sh /usr/local/bin/bestweb-entrypoint
RUN sed -i 's/\r$//' /usr/local/bin/bestweb-entrypoint \
    && chmod +x /usr/local/bin/bestweb-entrypoint

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_CACHE_DIR=/tmp/composer-cache

WORKDIR /var/www/html
EXPOSE 8000

ENTRYPOINT ["/usr/local/bin/bestweb-entrypoint"]
CMD ["serve"]
