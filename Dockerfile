FROM php:8.5-fpm-bookworm

# Installation des dépendances système et PostgreSQL
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpq-dev \
        git \
        unzip \
        curl \
    && docker-php-ext-install -j$(nproc) pdo pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

# Installation de Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Répertoire de travail du backend
WORKDIR /var/www/html

# Copie du backend dans l'image
COPY backend/ /var/www/html/

# Démarrage de PHP-FPM
CMD ["php-fpm"]