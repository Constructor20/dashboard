FROM php:8.2-apache

# Installer les dépendances pour PDO MySQL et Composer
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    zip \
    mariadb-client \
    libonig-dev \
    libzip-dev \
    default-mysql-client \
    && docker-php-ext-install pdo pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Activer mod_rewrite d’Apache
RUN a2enmod rewrite

WORKDIR /var/www/html
