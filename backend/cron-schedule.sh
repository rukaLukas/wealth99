#!/bin/bash
# Load environment variables
#source /var/www/.env
echo $DB_USERNAME
# Change to the project directory
cd /var/www
# Run the Laravel schedule
/usr/local/bin/php /var/www/artisan schedule:run

