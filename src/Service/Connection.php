<?php

namespace App\Service;

use App\Entity\Server;
use DivineOmega\SSHConnection\SSHConnection;

class Connection
{
    public function getConnection(Server $server): ?SSHConnection
    {
        try {
            $connection = (new SSHConnection())
                ->to($server->getIp())
                ->onPort($server->getPort())
                ->as($server->getLogin())
                ->withPassword($server->getPassword())
                ->connect();

            return $connection;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
