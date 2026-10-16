FROM php:8.3-apache

# 1. Instalar dependencias del sistema, extensiones PHP y Node.js 22 (compatible con Vite 8 / Vite+)
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    zip \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libsqlite3-dev \
    libpq-dev \
    libicu-dev \
    && curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        pdo_sqlite \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        intl \
        opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Habilitar mod_rewrite de Apache y apuntar DocumentRoot a /public
RUN a2enmod rewrite headers
ENV APACHE_DOCUMENT_ROOT=/var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf \
    && sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 3. Copiar Composer 2 oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 4. Copiar archivos del proyecto
COPY . /var/www/html

# 5. Instalar dependencias de PHP (Laravel 13 + Livewire 4 + DomPDF)
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist

# 6. Instalar dependencias Frontend y compilar assets con Vite 8
RUN npm install && npm run build && rm -rf node_modules

# 7. Preparar permisos y convertir saltos de línea CRLF (Windows) a LF (Linux)
RUN sed -i 's/\r$//' /var/www/html/docker/entrypoint.sh \
    && chmod +x /var/www/html/docker/entrypoint.sh \
    && mkdir -p storage/framework/sessions storage/framework/views storage/framework/cache/data database \
    && chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

EXPOSE 10000

ENTRYPOINT ["/var/www/html/docker/entrypoint.sh"]

