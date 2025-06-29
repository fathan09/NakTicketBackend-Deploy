FROM php:8.1-cli

WORKDIR /var/www

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev && \
    docker-php-ext-install zip pdo pdo_mysql

COPY api/ .

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN composer install || true

EXPOSE 3000

CMD ["php", "-S", "0.0.0.0:3000"]
