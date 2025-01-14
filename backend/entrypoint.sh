#!/bin/sh

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install --optimize-autoloader --no-interaction
else
  echo "Composer dependencies are already installed."
fi

# Copy .env.example to .env if .env does not exist
if [ ! -f ".env" ]; then
  echo "Copying .env.template to .env..."
  cp .env.template .env
  sleep 2
  php artisan key:generate
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache


# Wait for TimescaleDB to be ready
./wait-for-it.sh timescaledb --timeout=60 --strict -- echo "TimescaleDB is up and running"

# Run Laravel migrations and hypertable creation
php artisan migrate
php artisan convert:hypertable
php artisan db:seed

# Optimize
# php artisan config:cache
# php artisan route:cache
# php artisan view:cache

# RUN setup cron
# ./setup_cron.sh

# service cron start

service supervisor start

sleep 2
php artisan fire:recent

# Keep the container running
exec "$@"