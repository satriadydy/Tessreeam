FROM php:8.1-fpm-alpine

# Install nginx
RUN apk add --no-cache nginx

# Copy app files
COPY . /var/www/html

# Copy nginx config
COPY <<EOF /etc/nginx/nginx.conf
events {
    worker_connections 1024;
}

http {
    include /etc/nginx/mime.types;
    
    server {
        listen 80;
        root /var/www/html;
        index index.php index.html;
        
        location / {
            try_files \$uri \$uri/ /index.php?\$query_string;
        }
        
        location ~ \.php\$ {
            fastcgi_pass 127.0.0.1:9000;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
EOF

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# Start both nginx and php-fpm
CMD php-fpm -D && nginx -g 'daemon off;'
