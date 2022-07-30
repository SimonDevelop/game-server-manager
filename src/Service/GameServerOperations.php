<?php

namespace App\Service;

use App\Entity\GameServer;
use Doctrine\ORM\EntityManagerInterface;

class GameServerOperations
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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

        $this->em->persist($game);
        $this->em->flush();
    }
}
