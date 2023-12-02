#!/bin/bash
sleep 5

# Set TimeZone
ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Clear cache and update database
php bin/console c:c
php bin/console d:s:u --force --no-interaction

# Start cron
env >> /etc/environment
cron -f -l 2 &

# Start frankenphp
frankenphp run --config /etc/caddy/Caddyfile &

# Start supervisord
/usr/bin/supervisord &
  
# Wait for any process to exit
wait -n
  
# Exit with status of process that exited first
exit $?
