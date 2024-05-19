FROM php:8.2

WORKDIR /var/www/html

RUN apt-get update && apt-get install -y \
    git \
    nodejs \
    unzip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo_mysql zip


# Install necessary dependencies for MariaDB
RUN apt-get update && apt-get install -y mariadb-server mariadb-client

# Expose MariaDB port
EXPOSE 3306

# Start MariaDB service
CMD ["mysqld"]    


RUN docker-php-ext-install sockets

COPY composer.lock composer.json ./


COPY . .

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && chown -R www-data:www-data /var/www/html \
    && su www-data -s /bin/sh -c 'composer install --no-scripts --no-autoloader' \
    && composer dump-autoload \
    && chown -R root:root /var/www/html \
    && php artisan key:generate \
    && php artisan jwt:secret \
    && php artisan migrate \
    && php artisan db:seed \
    && npm run install \
    && npm run dev \
    && php artisan queue:work

EXPOSE 8080
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8080"]