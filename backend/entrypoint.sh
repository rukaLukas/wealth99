#!/bin/sh

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

./generate-docker-env.sh
# Run the main container command
exec "$@"