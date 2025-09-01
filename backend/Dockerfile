FROM php:8.2-apache

# Instalar dependencias del sistema y certificados SSL
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    ca-certificates \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/* \
    && update-ca-certificates

# Instalar extensiones PHP necesarias
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Habilitar mod_rewrite para URLs “bonitas” de Laravel
RUN a2enmod rewrite

# Configurar PHP para que reconozca certificados SSL (si tienes ajustes extra)
COPY docker/openssl.ini /usr/local/etc/php/conf.d/

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Instalar PHP_CodeSniffer globalmente
RUN composer global require squizlabs/php_codesniffer

# Añadir bin global de Composer al PATH
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Establecer directorio de trabajo
WORKDIR /var/www/html

# Copiar la aplicación Laravel
COPY . /var/www/html

# Permisos para storage y bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Exponer puerto 80
EXPOSE 80

# Arrancar Apache
CMD ["apache2-foreground"]
