ARG NODE_MAJOR=22
ARG PNPM_VERSION=9.15.9

FROM php:8.3-fpm-bookworm AS php-base

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        gosu \
        libfcgi-bin \
        libgmp-dev \
        libonig-dev \
        libpq-dev \
        libsqlite3-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-configure gmp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gmp \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        pgsql \
        soap \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/moon-entrypoint

RUN chmod +x /usr/local/bin/moon-entrypoint \
    && printf '[www]\nping.path = /ping\nping.response = pong\npm.status_path = /status\n' \
        > /usr/local/etc/php-fpm.d/zz-moon-healthcheck.conf

WORKDIR /var/www/html

ENTRYPOINT ["moon-entrypoint"]
CMD ["php-fpm"]

FROM php-base AS development

ARG NODE_MAJOR
ARG PNPM_VERSION
ARG APP_UID=1000
ARG APP_GID=1000

ENV HOME=/var/www
ENV MOON_PREPARE_DEV_VOLUMES=1

RUN groupmod --non-unique --gid "${APP_GID}" www-data \
    && usermod --non-unique --uid "${APP_UID}" --gid "${APP_GID}" www-data \
    && curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && corepack enable \
    && corepack prepare "pnpm@${PNPM_VERSION}" --activate \
    && mkdir -p \
        /var/www/.local/share/pnpm/store \
        /var/www/html/bootstrap/cache \
        /var/www/html/node_modules \
        /var/www/html/public/build \
        /var/www/html/storage/framework/cache \
        /var/www/html/storage/framework/sessions \
        /var/www/html/storage/framework/testing \
        /var/www/html/storage/framework/views \
        /var/www/html/storage/logs \
        /var/www/html/vendor \
    && chown -R www-data:www-data /var/www \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

FROM php-base AS php-dependencies

COPY . /var/www/html

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --optimize-autoloader \
    --prefer-dist

FROM node:22-bookworm-slim AS frontend-build

ARG PNPM_VERSION

WORKDIR /var/www/html

RUN corepack enable \
    && corepack prepare "pnpm@${PNPM_VERSION}" --activate

COPY package.json pnpm-lock.yaml ./
RUN pnpm install --frozen-lockfile

COPY . .
COPY --from=php-dependencies /var/www/html/vendor /var/www/html/vendor
RUN pnpm build

FROM php-base AS production

COPY --chown=www-data:www-data . /var/www/html
COPY --from=php-dependencies --chown=www-data:www-data /var/www/html/vendor /var/www/html/vendor
COPY --from=php-dependencies --chown=www-data:www-data /var/www/html/bootstrap/cache /var/www/html/bootstrap/cache
COPY --from=frontend-build --chown=www-data:www-data /var/www/html/public/build /var/www/html/public/build

RUN mkdir -p \
        bootstrap/cache \
        storage/framework/cache \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs \
    && chown -R www-data:www-data bootstrap/cache storage

FROM production AS staging

FROM nginx:1.27-alpine AS nginx

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend-build /var/www/html/public /var/www/html/public
