FROM php:8.2-apache

# 1. Mametraka ny extensions sy fitaovana ilaina
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libpq-dev \
    git \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql

# 2. Mametraka ny Composer (TENA ILAINA)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

RUN a2enmod rewrite

# 3. Adikao ny code
COPY . /var/www/html

# 4. Ampidiro ny dependances (vendor)
# Ampidirina koa ny --no-scripts mba tsy hisy erreur amin'ny voalohany
RUN composer install --no-dev --optimize-autoloader --no-scripts

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

EXPOSE 80

# 5. Ataovy ao anaty CMD ny migrate mba handeha isaky ny start
CMD sh -c "php artisan migrate --force && apache2-foreground"