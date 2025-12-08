#!/bin/bash
# docker/symfony/startup.sh

# Nombre del servicio de la DB y su puerto interno (del docker-compose.yml)
DB_HOST="database" 
DB_PORT="5432"

echo "Manually deleting Symfony cache..."
rm -rf var/cache/*

# 1. Función de espera (wait-for-it.sh simplificado)
wait_for_db() {
    echo "Esperando a que la base de datos (${DB_HOST}:${DB_PORT}) esté lista..."
    # El comando 'nc -z' verifica si el puerto está abierto
    until nc -z $DB_HOST $DB_PORT; do
        echo -n "."
        sleep 1
    done
    echo "¡Base de datos está lista!"
}

echo "Running Doctrine Migrations (Retrying if DB is starting up)..."
MAX_ATTEMPTS=10
ATTEMPT=0
# Se repite hasta que el comando de migración sea exitoso (exit code 0)
until php bin/console doctrine:migrations:migrate --no-interaction; do
    ATTEMPT=$((ATTEMPT+1))
    if [ $ATTEMPT -ge $MAX_ATTEMPTS ]; then
        echo "❌ Falló al ejecutar migraciones después de $MAX_ATTEMPTS intentos."
        exit 1
    fi
    echo "DB connection failed (still starting up?), retrying in 2 seconds..."
    sleep 2
done
echo "✅ Migraciones de Doctrine completadas."


echo "Updating Composer autoloader..."
php /usr/local/bin/composer dump-autoload --no-interaction

echo "Clearing Symfony cache..."
php bin/console cache:clear --no-warmup

# 2. Ejecutar las migraciones y cargar fixtures
run_migrations_and_fixtures() {
    echo "Ejecutando migraciones de Doctrine..."
    php bin/console doctrine:migrations:migrate --no-interaction
    
    # Opcional: Cargar Fixtures (datos iniciales como el usuario admin)
    # echo "Cargando fixtures (datos de prueba) si existen..."
    # php bin/console doctrine:fixtures:load --no-interaction
}

# --- Inicialización ---
wait_for_db
run_migrations_and_fixtures
echo "Executing custom command to create initial admin user..."
php bin/console app:create-admin-user

# 3. Ejecutar el comando principal del contenedor (ej. php-fpm)
# "$@" representa el comando que se pasa al entrypoint (ej. php-fpm)
exec "$@"