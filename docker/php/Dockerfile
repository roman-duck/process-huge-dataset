FROM php:8.3-fpm

RUN pecl install redis \
    && docker-php-ext-enable redis

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/process-huge-dataset

COPY . .

RUN chown -R www-data:www-data var

EXPOSE 9000
