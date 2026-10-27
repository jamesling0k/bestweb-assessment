#!/bin/sh
set -eu

if [ "${1:-}" = "serve" ]; then
    mkdir -p bootstrap/cache storage/app/private storage/logs \
        storage/framework/cache/data storage/framework/sessions storage/framework/views

    composer install --no-interaction --prefer-dist
    php artisan config:clear
    php artisan migrate --seed --force

    exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
fi

exec "$@"
