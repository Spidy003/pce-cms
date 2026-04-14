FROM php:8.2-apache

RUN a2enmod rewrite
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

COPY . /var/www/html/

RUN mkdir -p /var/www/html/uploads/submissions
RUN mkdir -p /var/www/html/uploads/profiles
RUN chown -R www-data:www-data /var/www/html/uploads
RUN chmod -R 777 /var/www/html/uploads

EXPOSE 80
