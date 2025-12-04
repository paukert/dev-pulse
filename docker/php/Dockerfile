# docker build -t php-dev-pulse -f docker/php/Dockerfile .

FROM php:8.4.15-apache

RUN apt-get update \
    && apt-get install -y zlib1g-dev build-essential gnupg ca-certificates libzip-dev libicu-dev g++ git zip curl libgd-dev libpng-dev libjpeg-dev libfreetype6-dev libpq-dev \
    && docker-php-ext-configure gd --with-external-gd --with-freetype --with-jpeg \
    && docker-php-ext-configure pcntl --enable-pcntl \
    && docker-php-ext-install pdo pdo_mysql mysqli pgsql pdo_pgsql intl zip gd bcmath sockets iconv opcache exif pcntl \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && echo ' \
        apc.enable=1 \n\
        apc.enable_cli=1 \
    ' >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini

# Copy PHP configuration file
#COPY ./docker/php.ini /usr/local/etc/php/php.ini

# Configure Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_startup_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "display_errors = On" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.mode=develop,debug" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo "xdebug.start_with_request=yes" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.client_host=host.docker.internal' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    && echo 'xdebug.idekey=xdebug' >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Composer
COPY --from=composer:2.9.2 /usr/bin/composer /usr/bin/composer

# Node
RUN curl -fsSL https://deb.nodesource.com/setup_22.x -o nodesource_setup.sh \
    && bash nodesource_setup.sh \
    && apt-get install -y nodejs \
    && rm nodesource_setup.sh

# Set document root
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Enable apache extensions
RUN a2enmod rewrite \
    && a2enmod headers
