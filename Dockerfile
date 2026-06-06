FROM php:8.2-apache

RUN docker-php-ext-install pdo pdo_mysql
RUN a2enmod rewrite

COPY . /var/www/html/

EXPOSE 80 

# this line is so we persist the uploads directory, which is where user-uploaded files will be stored. This way, even if the container is recreated, the uploaded files will not be lost.
VOLUME ["/var/www/html/uploads"]

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html