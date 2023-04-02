# game-server-manager
This web application allows you to control your game servers on multiple machines under Linux. The idea is to give you the means to remotely shutdown, start and update your game servers by sending ssh commands.

# Install with docker
Here is a docker-compose example (adapt the identifiers of the different containers) :
```
version: '3.1'

services:
  gsm:
    restart: always
    image: simondockerise/gsm:latest
    environment:
      - APP_ENV=prod
      - APP_SECRET=!CHangeMe!
      - PASSWORD_HASH_KEY=!CHangeMe!
      - IV_HASH=!CHangeMe!
      - DATABASE_URL=mysql://root:root@mysql:3306/gsm?serverVersion=5.7
      - MESSENGER_TRANSPORT_DSN=amqp://admin:admin@rabbitmq:5672/%2f/messages
      - REDIS_HOST=redis
      - REDIS_PORT=6379
    depends_on:
      - mysql
    ports:
      - 80:8080

  mysql:
    restart: always
    image: mysql:5.7
    command: ['mysqld', '--character-set-server=utf8mb4', '--collation-server=utf8mb4_unicode_ci']
    environment:
      - MYSQL_ROOT_PASSWORD=root
      - MYSQL_DATABASE=gsm

  redis:
    image: redis:7
    restart: always

  rabbitmq:
    restart: always
    image: rabbitmq:3-management-alpine
    environment:
      - RABBITMQ_DEFAULT_USER=admin
      - RABBITMQ_DEFAULT_PASS=admin
```
The project has been designed to run under docker with frankenphp, if you want to use it out of docker, remember to remove the `runtime/frankenphp-symfony` package and this part of the composer.json :
```
"runtime": {
    "class": "Runtime\\FrankenPhpSymfony\\Runtime"
}
```
# Crontab
You have a cron job to add for the verification of the game servers :
```
*/5 * * * * docker exec <container_app> php bin/console cron:server:check >/dev/null 2>&1
```

You can run up to 3 crontab commands to manage updates and restarts of your game servers when you want:
```
0 4 * * * docker exec <container_app> php bin/console cron:server:stop <id_game_server> >/dev/null 2>&1
1 4 * * * docker exec <container_app> php bin/console cron:server:update <id_game_server> --time=120 >/dev/null 2>&1
3 4 * * * docker exec <container_app> php bin/console cron:server:start <id_game_server> >/dev/null 2>&1
```

# Importante note
It is necessary to install your game server before adding it to GSM.