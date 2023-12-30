<?php

namespace App\Service;

use App\Entity\Server;
use phpseclib3\Net\SSH2;

class Connection
{
    public function __construct(private readonly ServerOperations $serverOperations)
    {
    }

    public function getConnection(Server $server): ?SSH2
    {
        try {
            $connection = new SSH2($server->getIp(), (int) $server->getPort());
            $connection->login($server->getLogin(), $server->getPassword());
            $this->serverOperations->updateLastConnection($server);

            return $connection;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function sendCommand(SSH2 $connection, string $command): bool
    {
        try {
            $connection->exec($command);

            return true;
        } catch (\Throwable $th) {
            return false;
        }   
    }

    public function sendCommandWithResponse(SSH2 $connection, string $command): ?string
    {
        try {
            return $connection->exec($command);
        } catch (\Throwable $th) {
            return null;
        }   
    }
}
