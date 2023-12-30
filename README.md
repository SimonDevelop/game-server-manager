[![version](https://img.shields.io/badge/Version-1.0.0-brightgreen.svg)](https://github.com/SimonDevelop/game-server-manager/releases/tag/1.0.0)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%208.2-8892BF.svg)](https://php.net/)
[![Github Actions PHP](https://github.com/SimonDevelop/game-server-manager/workflows/PHP/badge.svg)](https://github.com/SimonDevelop/game-server-manager/actions)
[![GitHub license](https://img.shields.io/badge/License-MIT-blue.svg)](https://github.com/SimonDevelop/game-server-manager/blob/master/LICENSE)

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
      - DATABASE_URL=postgresql://user:pass@postgres:5432/gsm?serverVersion=16&charset=utf8
      - TZ=Europe/Paris #default => America/Los_Angeles
      - APP_LANG=fr #default => en
    depends_on:
      - postgres
    ports:
      - 80:8080

  postgres:
    restart: always
    image: postgres:16
    environment:
      - POSTGRES_USER=user
      - POSTGRES_PASSWORD=pass
      - POSTGRES_DB=gsm
```
The project has been designed to run under docker with frankenphp, if you want to use it out of docker, remember to remove the `runtime/frankenphp-symfony` package and this part of the composer.json :
```
"runtime": {
    "class": "Runtime\\FrankenPhpSymfony\\Runtime"
}
```
# Cronjobs
You have a cron job for game servers verification that runs every 5 minutes directly in the container.

You can run up to 3 cronjobs commands to manage updates and restarts of your game servers from the interface.

If you want to manage cronjobs outside the application, you can add them like this:
```
0 4 * * * docker exec <container_app> php bin/console cron:server:stop <id_game_server> >> /var/log/cron.log 2>&1
1 4 * * * docker exec <container_app> php bin/console cron:server:update <id_game_server> --time=120 >> /var/log/cron.log 2>&1
3 4 * * * docker exec <container_app> php bin/console cron:server:start <id_game_server> >> /var/log/cron.log 2>&1
```

# Importante notes
- You need to install your game server before adding it to GSM.
- If the gsm container is refreshed, the cronjobs present in the base will be added to the container's crontab.
- Need to add your language? Don't hesitate to contribute with a pull request!