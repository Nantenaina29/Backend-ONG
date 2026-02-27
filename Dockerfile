# 1. Ampiasao ny PHP 8.2 miaraka amin'ny Apache
FROM php:8.2-apache

# 2. Mametraka ny extensions ilain'ny Laravel (PostgreSQL, GD, Zip, sns)
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql

# 3. Ampandehanina ny mod_rewrite an'ny Apache (Zava-dehibe amin'ny Routes sy CORS)
RUN a2enmod rewrite

# 4. Adikao ny kaody rehetra avy ao amin'ny folder-nao
COPY . /var/www/html

# 5. Omeo alalana ny folder Storage sy Cache (mba tsy hisy Error 500)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# 6. Lazao amin'ny Apache fa ao amin'ny /public ny fidirana (DocumentRoot)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 7. Port 80 no ampiasain'ny Apache ao anaty container
EXPOSE 80

# 8. Alefaso ny Apache
# Ovaina ho toy izao ny farany
CMD php artisan migrate --force && apache2-foreground