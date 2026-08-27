#!/bin/sh
set -e

php artisan migrate --force
php artisan db:seed --force

# Planificateur Laravel : réveille le scheduler chaque minute (équivalent du cron
# "* * * * * php artisan schedule:run" recommandé par Laravel), c'est lui qui
# déclenche l'envoi quotidien des rappels de renouvellement d'adhésion.
while true; do
    php artisan schedule:run >> /var/log/schedule.log 2>&1
    sleep 60
done &

exec apache2-foreground
