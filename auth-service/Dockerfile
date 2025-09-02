FROM php:8.2-cli

# Instala extensões e dependências
RUN apt-get update && apt-get install -y \
    git unzip curl zip \
    libpng-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql mbstring bcmath

# Instala Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Define diretório da aplicação
WORKDIR /app

# Copia aplicação
COPY src/ /app

# Ajusta permissões
RUN chown -R www-data:www-data /app/storage /app/bootstrap/cache

# Expõe a porta HTTP
EXPOSE 80