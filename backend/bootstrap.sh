#!/bin/bash

# Wait for TimescaleDB to be ready
./wait-for-it.sh timescaledb --timeout=60 --strict -- echo "TimescaleDB is up and running"

# Run Laravel migrations and hypertable creation
php artisan migrate
php artisan convert:hypertable
# php artisan db:seed

# RUN setup cron
./setup_cron.sh

service cron start

php artisan queue:work


# Keep the container running
exec "$@"
