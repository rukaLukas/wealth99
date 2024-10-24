#!/bin/bash

# Path to your Laravel project
PROJECT_PATH="/var/www"

# Check if cron job already exists
cron_exists=$(crontab -l | grep "$PROJECT_PATH/artisan schedule:run")

if [ -z "$cron_exists" ]; then
  # Append the new cron job to the crontab
  (crontab -l 2>/dev/null; echo "* * * * * /usr/local/bin/php /var/www/artisan schedule:run >> /var/www/cron.log 2>&1") | crontab -
  echo "Cron job added."
else
  echo "Cron job already exists."
fi