#!/usr/bin/env sh
set -e

cd /var/www/html

if [ ! -f database/database.sqlite ]; then
    touch database/database.sqlite
fi

php artisan migrate --force --seed
php artisan l5-swagger:generate

exec "$@"
