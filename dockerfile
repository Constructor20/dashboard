FROM php:8.2-apache

# Installer les dépendances nécessaires à Composer
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    curl \
    zip \
    && rm -rf /var/lib/apt/lists/*

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

# Activer mod_rewrite d’Apache (utile pour Laravel ou des routes personnalisées)
RUN a2enmod rewrite
