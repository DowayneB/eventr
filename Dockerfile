from php:8.2-fpm

RUN apt-get update && apt-get install -y \
    make gcc libonig-dev libmariadb-dev libmariadb-dev-compat libxml2-dev libzip-dev unzip \
    && docker-php-ext-install pdo pdo_mysql \
    && docker-php-ext-install mbstring xml ctype fileinfo

COPY . /var/www/html

WORKDIR /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install --no-dev --optimize-autoloader

CMD ["php-fpm"]
