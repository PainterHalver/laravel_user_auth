# Use the official PHP-FPM image based on Debian Bullseye
FROM php:8.2.4-fpm-bullseye

# Install system dependencies and PHP extensions
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
    git && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql sockets zip mbstring gd

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - && \
    apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/*

# Set the working directory to /app
WORKDIR /app

# Install packages first for better caching
COPY package*.json composer.* /app/
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install && \
    npm install && \
    npm run build

# Copy the application files into the container
COPY . /app

# Expose port 8000 for serving the application
EXPOSE 8000

# Start the PHP development server
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
