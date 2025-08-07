FROM php:8.1-apache

# Aktifkan mod_rewrite jika dibutuhkan
RUN a2enmod rewrite

# Salin semua file ke folder public web
COPY . /var/www/html/
