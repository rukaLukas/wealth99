#!/bin/bash

# Check if vendor directory exists
if [ ! -d "vendor" ]; then
  echo "Installing composer dependencies..."
  composer install
else
  echo "Composer dependencies are already installed."
fi

# Copy .env.example to .env if .env does not exist
if [ ! -f ".env" ]; then
  echo "Copying .env.example to .env..."
  cp .env.example .env
  php artisan key:generate
fi

chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache


# Wait for TimescaleDB to be ready
./wait-for-it.sh timescaledb --timeout=60 --strict -- echo "TimescaleDB is up and running"

# Run Laravel migrations and hypertable creation
php artisan migrate
php artisan convert:hypertable
php artisan db:seed

# RUN setup cron
./setup_cron.sh

service cron start

php artisan queue:work


# Keep the container running
exec "$@"
