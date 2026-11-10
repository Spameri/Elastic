ARG PHP_VERSION=8.2
FROM php:${PHP_VERSION}-cli

RUN apt-get update && apt-get install -y \
    curl \
    git \
    unzip \
    libcurl4-openssl-dev \
    && docker-php-ext-install curl iconv \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Xdebug config for coverage
RUN echo "xdebug.mode=coverage,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=trigger" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini
