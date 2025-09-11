FROM php:8.2-apache

# Instalar dependências e extensões PHP
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    zip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql \
    && pecl install apcu && docker-php-ext-enable apcu

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Definir o novo DocumentRoot do Apache
ENV APACHE_DOCUMENT_ROOT="/var/www/html/public"
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Definir o diretório de trabalho
WORKDIR /var/www/html

# Copiar apenas o necessário para o composer install
COPY composer.json .
# se existir
COPY composer.lock .   
COPY src/ ./src/

# Rodar o Composer install
RUN composer install --no-interaction --prefer-dist 

# Agora copiar o restante da aplicação (public/, tests/, etc.)
COPY public/ ./public/
COPY tests/ ./tests/
COPY phpunit.xml .


# Pronto! Container leve, limpo e funcional.
