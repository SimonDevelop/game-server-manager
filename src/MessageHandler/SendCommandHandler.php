<?php

namespace App\MessageHandler;

use App\Message\SendCommand;
use App\Repository\ServerRepository;
use App\Service\Connection;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendCommandHandler implements MessageHandlerInterface
{
    /**
     * @var ServerRepository
     */
    private $serverRepository;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(ServerRepository $serverRepository, Connection $connection)
    {
        $this->serverRepository = $serverRepository;
        $this->connection = $connection;
    }

    public function __invoke(SendCommand $message)
    {
        $server = $this->serverRepository->findById($message->getId());
        if (null === $server) {
            return 0;
        }

        $connection = $this->connection->getConnection($server);
        if (null === $connection) {
            return 0;
        }

        $this->connection->sendCommand($connection, $message->getCommand());
    }
}
