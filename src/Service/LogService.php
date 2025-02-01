<?php

namespace App\Service;

use App\Entity\GameServer;
use App\Entity\Log;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class LogService
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function addLog(GameServer $gameServer, string $action, bool $state = true, ?User $user = null): void
    {
        $log = new Log();
        $log->setAction($action);
        if (null !== $user) {
            $log->setUser($user);
        }
        $log->setGameServer($gameServer);
        $log->setState($state);
        $log->setCreatedAt(new DateTime());

        $this->em->persist($log);
        $this->em->flush();
    }
}
