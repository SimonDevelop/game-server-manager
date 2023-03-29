# game-server-manager
This web application allows you to control your game servers on multiple machines under Linux. The idea is to give you the means to remotely shutdown, start and update your game servers by sending ssh commands.

# Install with docker
You have at your disposal a sample file `docker-compose.yaml.dist` to launch the project instantly with docker.

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
It is necessary to install your game servers beforehand and to provide the update scripts (optional).

After installation of your game server, create `server_logs.conf` file config in the folder with content :
```
logfile /path/of/gameserver/folder/server.log
logfile flush 1
log on
```
It is imperative that your log file be named `server.log`.

We hope to manage this part independently in the future.
