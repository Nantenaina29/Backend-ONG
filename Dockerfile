FROM php:8.2-apache

# 1. Extensions sy Fitaovana
RUN apt-get update && apt-get install -y \
    libpng-dev libjpeg-dev libfreetype6-dev \
    zip unzip libpq-dev git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql

# 2. Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 3. ACTIVER REWRITE SY HEADERS (Tena zava-dehibe amin'ny CORS)
RUN a2enmod rewrite headers

# 4. Code sy Permissions
COPY . /var/www/html
RUN composer install --no-dev --optimize-autoloader --no-scripts
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 5. DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80

CMD sh -c "php artisan migrate --force && apache2-foreground"