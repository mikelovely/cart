FROM php:7.2-fpm-alpine

RUN apk add --no-cache --virtual .dd-build-deps $PHPIZE_DEPS python3-dev openssl-dev libffi-dev \
    && apk add --no-cache python3 openssl \
        php7-openssl \
        ca-certificates \
    && docker-php-ext-install pdo_mysql sockets \
    && pecl install -of redis \
    && docker-php-ext-enable redis \
    && pip3 install --upgrade pip \
    && pip3 install credstash \
    && apk del .dd-build-deps

ADD fpm/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
