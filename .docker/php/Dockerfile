FROM php:8.0.0-apache
RUN apt-get update -y

RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod env
RUN a2enmod auth_basic
RUN a2enmod rewrite

RUN chown -R www-data:www-data /var/www/

RUN pecl install xdebug && docker-php-ext-enable xdebug

RUN apt-get update && \
    apt-get install -y apache2-utils && \
    rm -rf /var/lib/apt/lists/*

RUN htpasswd -cb /etc/apache2/.htpasswd admin admin && \
    htpasswd -b /etc/apache2/.htpasswd manager manager_password && \
    htpasswd -b /etc/apache2/.htpasswd user1 user1_password && \
    htpasswd -b /etc/apache2/.htpasswd jarek user1_password && \
    htpasswd -b /etc/apache2/.htpasswd maciek maciek_password && \
    chmod 644 /etc/apache2/.htpasswd



WORKDIR /var/www/html

EXPOSE 80 443
