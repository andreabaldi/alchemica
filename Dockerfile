# Stato: COMPLETO (LAMP Stack in Single Container)
FROM php:8.2-apache

# 1. Installazione librerie, ImageMagick e MySQL Server
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    fonts-dejavu fontconfig \
    libicu-dev libzip-dev libpng-dev libjpeg-dev libfreetype6-dev \
    git unzip default-mysql-server \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install intl zip pdo_mysql gd \
    && rm -rf /var/lib/apt/lists/* \
    && fc-cache -f -v

# 2. Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# 3. Abilitiamo Rewrite
RUN a2enmod rewrite

# 4. Cartella di lavoro
WORKDIR /var/www/html

# 5. Copia del codice
COPY . .

# 6. FIX COMPOSER
RUN git config --global --add safe.directory /var/www/html \
    && composer install --no-interaction --optimize-autoloader --no-scripts --ignore-platform-reqs
    
# 7. CONFIGURAZIONE APACHE
RUN sed -ri -e 's!/var/www/html!/var/www/html/frontend/web!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!/var/www/html/frontend/web!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 8. Sblocco permessi Apache
RUN echo "<Directory /var/www/html/frontend/web/>\n\
    Options Indexes FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>" > /etc/apache2/conf-available/yii2.conf \
    && a2enconf yii2

# 9. Cartelle Runtime e Permessi
RUN mkdir -p frontend/runtime frontend/web/assets frontend/web/uploads/targets \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 777 frontend/runtime frontend/web/assets frontend/web/uploads

# 10. Script di avvio per far partire sia MySQL che Apache
RUN echo "#!/bin/bash\n\
service mysql start\n\
# Creazione DB e Utente se non esistono\n\
mysql -e \"CREATE DATABASE IF NOT EXISTS alchemica;\"\n\
apache2-foreground" > /usr/local/bin/start.sh \
    && chmod +x /usr/local/bin/start.sh

EXPOSE 80
CMD ["/usr/local/bin/start.sh"]