FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Enable mod_rewrite for Laravel's pretty URLs
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Install PHP_CodeSniffer globally
RUN composer global require squizlabs/php_codesniffer

# Add Composer global bin to PATH
ENV PATH="${PATH}:/root/.composer/vendor/bin"

# Set working directory
WORKDIR /var/www/html

# Copy existing application directory contents
COPY . /var/www/html

# Ensure proper permissions for storage and bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache || true

# Expose port 80
EXPOSE 80

# Set the entrypoint
CMD ["apache2-foreground"]
