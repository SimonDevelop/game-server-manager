#!/bin/bash
sleep 10
# Clear cache and update database
php bin/console c:c
php bin/console d:s:u --force --no-interaction

# Start the first process
frankenphp run --config /etc/Caddyfile &

# Start the second process
/usr/bin/supervisord &
  
# Wait for any process to exit
wait -n
  
# Exit with status of process that exited first
exit $?
