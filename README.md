# game-server-manager
This web application allows you to control your game servers on multiple machines under Linux. The idea is to give you the means to remotely shutdown, start and update your game servers by sending ssh commands.

For the installation of this web application, you can refer to the github repository [docker-gsm](https://github.com/SimonDevelop/docker-gsm) 

It is necessary to install your game servers beforehand and to provide the update scripts (optional).

After installation of your game server, create `server_logs.conf` file config in the folder with content :
```
logfile /path/of/gameserver/folder/server.log
logfile flush 1
log on
```
It is imperative that your log file be named `server.log`.
