FROM php:8.3-fpm

ENV DEBIAN_FRONTEND=noninteractive
ENV PNPM_HOME="/pnpm"
ENV PATH="$PNPM_HOME:$PATH"

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libpq-dev \
    libzip-dev \
    libgmp-dev \
    libonig-dev \
    libxml2-dev \
    curl \
    ca-certificates \
    && curl -fsSL https://nodesource.com | bash - \
    && apt-get install -y nodejs \
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
RUN curl -fsSL https://get.pnpm.io/install.sh | sh -

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

CMD ["php-fpm"]
