FROM php:8.1-apache

# Enable required extensions
RUN docker-php-ext-install dom

# Copy files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80
