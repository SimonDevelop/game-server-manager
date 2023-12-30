<?php

namespace App\Service;

use App\Entity\Server;
use DivineOmega\SSHConnection\SSHConnection;

class Connection
{
    public function __construct(private readonly ServerOperations $serverOperations)
    {
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

    public function sendCommandWithResponse(SSHConnection $connection, string $command): ?string
    {
        try {
            $cmd = $connection->run($command);
            return $cmd->getOutput();
        } catch (\Throwable $th) {
            return null;
        }   
    }
}
