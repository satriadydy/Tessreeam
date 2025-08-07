FROM php:8.1-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions (curl sudah tersedia secara default)
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Enable Apache mod_rewrite untuk framework PHP
RUN a2enmod rewrite

# Copy aplikasi ke container
COPY . /var/www/html/

# Set permissions yang benar
RUN chown -R www-data:www-data /var/www/html/

# Railway membutuhkan expose port
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
