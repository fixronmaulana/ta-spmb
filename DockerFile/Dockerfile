FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    curl \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        gd \
        pdo \
        pdo_mysql \
        mysqli \
        intl \
        zip \
        opcache

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable Apache mod_rewrite (wajib untuk CI4)
RUN a2enmod rewrite

# Arahkan Apache ke folder /public milik CI4
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' \
        /etc/apache2/apache2.conf \
        /etc/apache2/conf-available/*.conf

# Set working directory
WORKDIR /var/www/html

# Copy semua file proyek
COPY . .

# Install dependencies PHP (tanpa package dev)
RUN composer install --no-dev --optimize-autoloader

# Beri akses tulis ke folder writable dan uploads
RUN chown -R www-data:www-data writable/ public/uploads/ \
    && chmod -R 775 writable/ public/uploads/

EXPOSE 80
