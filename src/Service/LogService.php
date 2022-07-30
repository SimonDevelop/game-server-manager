<?php

namespace App\Service;

use App\Entity\GameServer;
use App\Entity\Log;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;

class LogService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addLog(?User $user = null, GameServer $gameServer, string $action, bool $state = true)
    {
        $log = new Log();
        $log->setAction($action);
        if (null !== $user) {
            $log->setUser($user);
        }
        $log->setGameServer($gameServer);
        $log->setState($state);
        $log->setCreatedAt(new DateTimeImmutable());

        $this->em->persist($log);
        $this->em->flush();
    }
}
