FROM php:8.3-cli

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-install zip gd

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

RUN chmod -R 775 storage bootstrap/cache

CMD php artisan serve --host 0.0.0.0 --port ${PORT}
