# Use the official PHP-FPM image based on Debian Bullseye
FROM php:8.2.4-fpm-bullseye AS base

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
    gnupg \
    unzip \
    git && \
    rm -rf /var/lib/apt/lists/* && \
    docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install pdo pdo_mysql sockets zip mbstring gd

# Install Composer globally
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Node.js and npm
RUN mkdir -p /etc/apt/keyrings && \
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg && \
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_18.x nodistro main" | tee /etc/apt/sources.list.d/nodesource.list && \
    apt-get update && apt-get install -y nodejs && \
    rm -rf /var/lib/apt/lists/* && \
    npm install -g yarn

# Set the working directory to /app
WORKDIR /app

FROM base AS development
# Entry point file
RUN cat <<EOF > /scripts/entrypoint.sh
#!/bin/bash
set -e
npm run dev &
php artisan serve --host=0.0.0.0 --port=8000
EOF

COPY . /app
RUN --mount=type=cache,target=~/.npm \
    --mount=type=cache,target=~/.composer \
    COMPOSER_ALLOW_SUPERUSER=1 composer install && \
    yarn install
EXPOSE 8000
CMD ["chmod", "+x", "/scripts/entrypoint.sh"]
ENTRYPOINT ["/scripts/entrypoint.sh"]

FROM base AS production
COPY . /app
RUN --mount=type=cache,target=~/.npm \
    --mount=type=cache,target=~/.composer \
    COMPOSER_ALLOW_SUPERUSER=1 composer install && \
    yarn install --frozen-lockfile && \
    npm run build
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
