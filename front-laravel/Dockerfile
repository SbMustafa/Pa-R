# --- Etape 1 : installation de Laravel + ajout de nos fichiers custom ---
FROM composer:2 AS build
WORKDIR /app

RUN composer config --global policy.advisories.block false \
    && composer create-project laravel/laravel:^11.0 . --prefer-dist --no-interaction --no-progress --no-dev --no-audit \
    && composer require barryvdh/laravel-dompdf:^3.0 phpoffice/phpspreadsheet:^3.0 \
        --no-interaction --no-progress --no-audit --ignore-platform-req=ext-gd

# Copie de nos fichiers applicatifs par-dessus le squelette Laravel généré
COPY overrides/ /tmp/overrides/
RUN cp -r /tmp/overrides/app/. /app/app/ \
    && cp -r /tmp/overrides/routes/. /app/routes/ \
    && cp -r /tmp/overrides/resources/. /app/resources/ \
    && cp -r /tmp/overrides/database/. /app/database/ \
    && cp -r /tmp/overrides/bootstrap/. /app/bootstrap/ \
    && mkdir -p /app/lang && cp -r /tmp/overrides/lang/. /app/lang/ \
    && cat /tmp/overrides/env.docker.append >> /app/.env \
    && rm -rf /tmp/overrides \
    && php artisan key:generate --force

# --- Etape 2 : image d'exécution Apache + PHP ---
FROM php:8.4-apache

# gd est requis par phpoffice/phpspreadsheet (génération des plannings Excel)
RUN a2enmod rewrite \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev libonig-dev unzip libpng-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath zip gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=build /app /var/www/html
COPY apache-laravel.conf /etc/apache2/conf-available/z-laravel.conf
COPY docker-entrypoint.sh /docker-entrypoint.sh
RUN a2enconf z-laravel \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf /etc/apache2/apache2.conf \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache \
    && chmod +x /docker-entrypoint.sh

EXPOSE 80
ENTRYPOINT ["/docker-entrypoint.sh"]
