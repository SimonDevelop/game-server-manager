<?php

namespace App\Service;

use App\Entity\Server;
use DivineOmega\SSHConnection\SSHConnection;

class Connection
{
    private ServerOperations $serverOperations;

    public function __construct(ServerOperations $serverOperations)
    {
        $this->serverOperations = $serverOperations;
    }

    public function getConnection(Server $server): ?SSHConnection
    {
        try {
            $connection = (new SSHConnection())
                ->to($server->getIp())
                ->onPort($server->getPort())
                ->as($server->getLogin())
                ->withPassword($server->getPassword())
                ->connect();

            $this->serverOperations->updateLastConnection($server);
            return $connection;
        } catch (\Throwable $th) {
            return null;
        }
    }

    public function sendCommand(SSHConnection $connection, string $command): bool
    {
        try {
            $connection->run($command);
            return true;
        } catch (\Throwable $th) {
            return false;
        }   
    }
}
