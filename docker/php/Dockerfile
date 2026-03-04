FROM php:8.4-fpm

RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install pdo pdo_mysql zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /var/log/app && chmod 777 /var/log/app

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
