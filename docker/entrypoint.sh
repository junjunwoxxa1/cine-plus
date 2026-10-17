#!/usr/bin/env bash
set -e

# Ajustar el puerto de Apache al puerto dinámico de Render ($PORT, por defecto 10000)
PORT="${PORT:-10000}"
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80>/:${PORT}>/g" /etc/apache2/sites-available/*.conf

cd /var/www/html

# Si no existe .env, crear desde .env.example
if [ ! -f ".env" ]; then
    cp .env.example .env
fi

# Si se usa SQLite (ideal para despliegue 100% gratuito en Render), asegurar que el archivo exista
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
    mkdir -p /var/www/html/database
    if [ ! -f "/var/www/html/database/database.sqlite" ]; then
        if [ -f "/var/www/html/database/seed.sqlite" ]; then
            echo "Copiando base de datos exacta de local (seed.sqlite)..."
            cp /var/www/html/database/seed.sqlite /var/www/html/database/database.sqlite
        else
            touch /var/www/html/database/database.sqlite
        fi
    fi
    chown -R www-data:www-data /var/www/html/database
    chmod -R 775 /var/www/html/database
fi

# Generar APP_KEY automáticamente si no fue definida en las variables de entorno de Render
if [ -z "${APP_KEY}" ]; then
    php artisan key:generate --force --no-interaction
fi

# Crear enlace simbólico de storage
php artisan storage:link --force || true

# Ejecutar migraciones automáticamente
php artisan migrate --force --no-interaction

# Asegurar roles y usuarios por defecto (Admin, Cajero, Comprador con 250 puntos)
php artisan db:seed --class=AdminUserSeeder --force --no-interaction

# Poblar la base de datos inicial (películas, salas, funciones, dulcería) si está vacía
PELICULAS_COUNT=$(php artisan tinker --execute="echo \App\Models\Pelicula::count();" 2>/dev/null | tr -d '[:space:]' || echo "0")
if [ "$PELICULAS_COUNT" = "0" ] || [ -z "$PELICULAS_COUNT" ]; then
    echo "Cargando datos iniciales de Cine+ (Seeders)..."
    php artisan db:seed --force --no-interaction
fi

# Optimizar caché de Laravel para producción
php artisan config:clear
php artisan view:clear
php artisan route:clear

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache /var/www/html/database

echo "Cine+ listo en el puerto ${PORT}"
exec apache2-foreground

