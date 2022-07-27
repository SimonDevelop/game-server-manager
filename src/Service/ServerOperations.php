<?php

namespace App\Service;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ServerOperations
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function updateLastConnection(Server $server)
    {
        $server->setLastConnection(new DateTimeImmutable());
        $this->entityManager->persist($server);
        $this->entityManager->flush();
    }
}
