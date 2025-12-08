# backend/Dockerfile

# Usar una imagen base de PHP optimizada
FROM php:8.3-fpm-alpine

# Instalar dependencias del sistema necesarias para Symfony y la base de datos
RUN apk add --no-cache \
    git \
    make \
    curl \
    postgresql-dev \
    mysql-client

# Instalar extensiones PHP comunes (pdo_pgsql y pdo_mysql son opcionales según la DB)
RUN docker-php-ext-install pdo pdo_pgsql pdo_mysql

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Definir el directorio de trabajo
WORKDIR /var/www/symfony

# Copiar el código de la aplicación
# NOTA: En desarrollo, usaremos VOLUMES en docker-compose para montar el código en vivo.
# Esto es principalmente para la construcción final de la imagen.
COPY . /var/www/symfony

# Instalar dependencias de Symfony
RUN php -d memory_limit=-1 /usr/local/bin/composer install --no-dev --optimize-autoloader --no-interaction --no-scripts
RUN apk update && apk add netcat-openbsd

# Exponer el puerto de FPM (por defecto 9000)
EXPOSE 9000

# Comando para iniciar PHP-FPM
CMD ["php-fpm"]