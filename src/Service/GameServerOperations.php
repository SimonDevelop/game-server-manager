<?php

namespace App\Service;

use App\Entity\GameServer;
use Doctrine\ORM\EntityManagerInterface;

class GameServerOperations
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getGameServerNameScreen(GameServer $game): string
    {
        return 'gameserver_'.$game->getId();
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

        if ($game->getState() === 'Installing' || $game->getState() === 'Updating') {
            $game->setStateType(0);
        }

        $this->entityManager->persist($game);
        $this->entityManager->flush();
    }
}
