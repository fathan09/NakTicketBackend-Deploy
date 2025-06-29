FROM php:8.1-cli

WORKDIR /var/www/api

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev && \
    docker-php-ext-install zip pdo pdo_mysql

# Copy your project (including composer.json in api/)
COPY api/ /var/www/api

# Copy composer binary
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install dependencies inside api/ folder
RUN composer install

EXPOSE 9000

CMD ["php", "-S", "0.0.0.0:9000", "-t", "/var/www/api"]


