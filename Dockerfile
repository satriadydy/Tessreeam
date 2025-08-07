FROM php:8.1-apache

# Aktifkan mod_rewrite (jika perlu)
RUN a2enmod rewrite

# Salin semua file ke direktori hosting Apache
COPY . /var/www/html/
