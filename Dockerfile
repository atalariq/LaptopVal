# PHP 8.3 + Apache for Used Laptop Deal Evaluator
FROM php:8.3-apache

# mysqli for DB access
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Enable Apache rewrite (clean URLs if needed later)
RUN a2enmod rewrite

WORKDIR /var/www/html
