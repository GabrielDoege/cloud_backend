FROM php:8.2-cli

RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath

COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app

COPY src/ /app

RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

EXPOSE 80






