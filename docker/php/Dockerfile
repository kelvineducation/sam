FROM php:8.1.8-fpm-alpine

# git is for composer
# zip is for composer

RUN apk add --no-cache --virtual .build-deps \
        $PHPIZE_DEPS \
    && apk add --no-cache \
        git \
        postgresql-dev \
        postgresql-client \
        libzip-dev \
    && docker-php-ext-install \
        pgsql \
        zip \
    && pecl install redis \
        && docker-php-ext-enable redis \
    && apk del --no-cache .build-deps

RUN curl -sS https://getcomposer.org/installer >composer-setup.php \
    && php composer-setup.php --quiet \
        --install-dir=/usr/local/bin --filename=composer \
    && rm composer-setup.php

RUN curl -sS https://raw.githubusercontent.com/eficode/wait-for/master/wait-for > /usr/local/bin/wait-for \
    && chmod 0755 /usr/local/bin/wait-for

WORKDIR /app
