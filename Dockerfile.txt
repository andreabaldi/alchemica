# Stato: COMPLETO (Yii2 + Imagick + DigitalOcean Fix)
FROM php:8.2-apache

# 1. Installazione dipendenze e ImageMagick
RUN apt-get update && apt-get install -y \
    libmagickwand-dev --no-install-recommends \
    fonts-dejavu-core \
    fonts-dejavu \
    fontconfig \
    libicu-dev \
    libzip-dev \
    libfreetype6-dev \
    libfontconfig1-dev \
    && pecl install imagick \
    && docker-php-ext-enable imagick \
    && docker-php-ext-install intl zip pdo_mysql \
    && rm -rf /var/lib/apt/lists/* \
    && fc-cache -f -v

# 2. Abilitiamo il modulo rewrite di Apache
RUN a2enmod rewrite

# 3. Impostazione cartella di lavoro
WORKDIR /app

# 4. Configurazione Apache per Yii2 (Punta alla cartella /web)
ENV APACHE_DOCUMENT_ROOT /app/frontend/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/000-default.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/conf-available/*.conf

# 5. Copia del codice
COPY . /app

# 6. FIX PERMESSI PER IL CLOUD (Importante!)
# Su DigitalOcean usiamo www-data (utente standard) invece di root per sicurezza
RUN chown -R www-data:www-data /app \
    && mkdir -p /app/frontend/runtime /app/frontend/web/assets /app/frontend/web/uploads/targets \
    && chmod -R 777 /app/frontend/runtime /app/frontend/web/assets /app/frontend/web/uploads

# 7. FIX PER L'ENTRYPOINT (Risolve l'errore che hai visto nel deploy)
RUN chmod +x /usr/local/bin/docker-php-entrypoint

EXPOSE 80

CMD ["apache2-foreground"]