FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    curl \
    zip

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/process-huge-dataset

COPY . .

EXPOSE 9000
