FROM php:8.1-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev && \
    docker-php-ext-install zip pdo pdo_mysql

COPY . .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 🔧 Change to the API folder before installing dependencies
WORKDIR /var/www/api
RUN composer install || true

EXPOSE 9000

CMD ["php", "-S", "0.0.0.0:9000", "-t", "/var/www/api"]


