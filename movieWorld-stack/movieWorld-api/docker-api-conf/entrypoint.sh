#!/bin/bash

# Download Laravel dependencies using Composer
cd /var/www/html && composer install

# Give Apache ownership of Laravel storage folder
chown -R www-data:www-data /var/www/html/storage

# Give write permission of Laravel storage folder to Apache group so that we can clean the logs
chmod -R g+w /var/www/html/storage

# Start Cron Daemon
env >> /etc/environment && cron

# Start Apache
apache2-foreground
