FROM php:8.4-cli

# Instalar utilidades del sistema necesarias para Composer
RUN apt-get update && apt-get install -y git unzip libzip-dev && docker-php-ext-install zip

# Incorporar Composer oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar los archivos del proyecto al contenedor
COPY . .

# Instalar dependencias de Laravel
RUN composer install --optimize-autoloader --no-dev

# Preparar almacenamiento local y permisos
RUN mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache \
    && touch database/database.sqlite \
    && chmod -R 775 storage bootstrap/cache database

# Iniciar el servidor de Laravel en el puerto dinámico de Render
CMD php artisan config:clear && php artisan route:clear && php artisan serve --host=0.0.0.0 --port=${PORT:-10000}