#!/bin/sh
set -eu

repair_owner() {
    path="$1"

    mkdir -p "${path}"
    find "${path}" \( ! -user www-data -o ! -group www-data \) \
        -exec chown www-data:www-data {} +
}

if [ "$(id -u)" = '0' ]; then
    repair_owner /var/www/html/bootstrap/cache
    repair_owner /var/www/html/storage/framework/cache
    repair_owner /var/www/html/storage/framework/sessions
    repair_owner /var/www/html/storage/framework/testing
    repair_owner /var/www/html/storage/framework/views
    repair_owner /var/www/html/storage/logs

    if [ "${MOON_PREPARE_DEV_VOLUMES:-0}" = '1' ]; then
        repair_owner /var/www/.local/share/pnpm/store
        repair_owner /var/www/html/node_modules
        repair_owner /var/www/html/public/build
        repair_owner /var/www/html/vendor
    fi

    if [ "${1:-}" = 'php-fpm' ]; then
        exec "$@"
    fi

    exec gosu www-data "$@"
fi

exec "$@"
