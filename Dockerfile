FROM php:8.1-apache

# Copy semua file dari direktori sekarang ke dalam /var/www/html
COPY . /var/www/html/
