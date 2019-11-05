FROM php:7.2-apache

RUN pecl install apcu \
 && pecl install xdebug
RUN apt-get update -y
RUN apt-get install -y --no-install-recommends \
    git \
    libjpeg-dev \
    libpng-dev \
    libzip-dev \
    libicu-dev \
    zip \
    zlib1g-dev \
 && docker-php-ext-configure gd --with-jpeg-dir=/usr/include/ \
 && docker-php-ext-configure intl \
 && docker-php-ext-configure zip --with-libzip \
 && docker-php-ext-enable apcu opcache xdebug \
 && docker-php-ext-install -j$(nproc) gd intl mbstring pdo_mysql zip \
 && curl --silent --show-error https://getcomposer.org/installer | php \
 && mv composer.phar /usr/local/bin/composer
RUN a2enmod rewrite
