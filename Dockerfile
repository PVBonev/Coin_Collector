# we use official image of PHP with Apache
FROM php:8.2-apache

# installing required PHP extensions for MySQL
RUN docker-php-ext-install pdo pdo_mysql

# turn on mod_rewrite for Apache so the urls work properly
RUN a2enmod rewrite

# copy the project files to the Apache document root
COPY . /var/www/html/

# we set the permissions so that Apache can access the files(especially uploads)
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html