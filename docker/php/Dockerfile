FROM php:8.3-fpm-bookworm

# Pin frontend tools so every developer builds with the same versions.
ARG NODE_MAJOR=22
ARG PNPM_VERSION=9.15.9

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        gnupg \
        libgmp-dev \
        libonig-dev \
        libpq-dev \
        libxml2-dev \
        libzip-dev \
        unzip \
    && curl -fsSL "https://deb.nodesource.com/setup_${NODE_MAJOR}.x" | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && docker-php-ext-configure gmp \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        gmp \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        soap \
        zip \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && corepack enable \
    && corepack prepare "pnpm@${PNPM_VERSION}" --activate \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/pear

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
