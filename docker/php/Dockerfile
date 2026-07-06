FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
        git \
        unzip \
        libpq-dev \
        libzip-dev \
        libgmp-dev \
        libonig-dev \
        libxml2-dev \
    && docker-php-ext-configure gmp \
    && docker-php-ext-install \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        gmp \
        soap \
        bcmath \
        zip \
        pcntl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
