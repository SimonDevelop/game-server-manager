<?php

namespace App\Service;

use App\Entity\Server;
use Doctrine\ORM\EntityManagerInterface;
use DateTimeImmutable;

class ServerOperations
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function updateLastConnection(Server $server)
    {
        $server->setLastConnection(new DateTimeImmutable());
        $this->em->persist($server);
        $this->em->flush();
    }
}
