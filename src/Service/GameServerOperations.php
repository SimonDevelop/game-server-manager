<?php

namespace App\Service;

use App\Entity\GameServer;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Connection;

class GameServerOperations
{
    private EntityManagerInterface $em;

    private Connection $connection;

    public function __construct(EntityManagerInterface $em, Connection $connection)
    {
        $this->em         = $em;
        $this->connection = $connection;
    }

    public function getGameServerNameScreen(GameServer $game): string
    {
        return 'gameserver_'.$game->getId();
    }

    public function getGameServerLogConf(GameServer $game): string
    {
        $name = "server_logs.conf";
        $path = $game->getPath();
        if (substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }
        return "$path/$name";
    }

    public function getGameServerLog(GameServer $game): string
    {
        $name = "server.log";
        $path = $game->getPath();
        if (substr($path, -1) === '/') {
            $path = substr($path, 0, -1);
        }
        return "$path/$name";
    }

    public function setStateAfterUpdate(GameServer $game): void
    {
        if ($game->getState() === 'Starting') {
            sleep(10);
            $game->setStateType(1);
        }

        if ($game->getState() === 'Stopping') {
            sleep(10);
            $game->setStateType(0);
        }

        if ($game->getState() === 'Updating') {
            sleep(10);
            $game->setStateType(0);
        }

        $this->em->persist($game);
        $this->em->flush();
    }

    public function setStateAfterUpdateFailed(GameServer $game): void
    {
        if ($game->getState() === 'Starting') {
            sleep(10);
            $game->setStateType(0);
        }

        if ($game->getState() === 'Stopping') {
            sleep(10);
            $game->setStateType(1);
        }

        if ($game->getState() === 'Updating') {
            sleep(10);
            $game->setStateType(0);
        }

        $this->em->persist($game);
        $this->em->flush();
    }
    
    public function createLogConfig(GameServer $game): bool
    {
        $pathConf   = $this->getGameServerLogConf($game);
        $pathLog    = $this->getGameServerLog($game);
        $connection = $this->connection->getConnection($game->getServer());

        $rm                 = "rm $pathConf $pathLog";
        $echoConfFirstLine  = "echo 'logfile $pathLog' > $pathConf";
        $echoConfSecondLine = "echo 'logfile flush 1' >> $pathConf";
        $echoConfThreeLine  = "echo 'log on' >> $pathConf";
        $createLogFile      = "touch $pathLog";
        $cmd                = "$rm && $echoConfFirstLine && $echoConfSecondLine && $echoConfThreeLine && $createLogFile";
        
        return $this->connection->sendCommand($connection, $cmd);
    }
}
