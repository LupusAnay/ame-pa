FROM php:7-apache
RUN a2enmod rewrite && docker-php-ext-install pdo pdo_mysql mysqli