<?php

namespace App\Service;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ServerOperations
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    public function updateLastConnection(Server $server): void
    {
        $server->setLastConnection(new DateTimeImmutable());
        $this->em->persist($server);
        $this->em->flush();
    }
}
