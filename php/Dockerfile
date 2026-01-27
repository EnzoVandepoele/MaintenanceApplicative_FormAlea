FROM php:8.3-apache

# Extensions utiles (pdo_mysql pour MySQL)
RUN docker-php-ext-install pdo pdo_mysql

# (Optionnel) activer mod_rewrite si tu en as besoin
RUN a2enmod rewrite
