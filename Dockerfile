FROM php:8.2.4-fpm-bullseye

RUN apt-get update && apt-get install -y \
    libonig-dev \
    libpq-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    zip \
    curl \
    unzip \
    git
RUN docker-php-ext-install pdo pdo_mysql sockets zip mbstring gd
RUN curl -sS https://getcomposer.org/installer | php -- \
     --install-dir=/usr/local/bin --filename=composer

RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - &&\
    apt-get install -y nodejs

WORKDIR /app
COPY . /app
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install
RUN php artisan migrate:fresh --seed
RUN npm install
RUN npm run build

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
