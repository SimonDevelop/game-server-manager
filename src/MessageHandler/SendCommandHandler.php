<?php

namespace App\MessageHandler;

use App\Message\SendCommandMessage;
use App\Repository\GameServerRepository;
use App\Service\Connection;
use App\Service\GameServerOperations;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendCommandHandler
{
    /**
     * @var GameServerRepository
     */
    private $gameRepository;

    /**
     * @var GameServerOperations
     */
    private $gameOperations;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(
        GameServerRepository $gameRepository,
        GameServerOperations $gameOperations,
        Connection $connection
    )
    {
        $this->gameRepository = $gameRepository;
        $this->gameOperations = $gameOperations;
        $this->connection     = $connection;
    }

    public function __invoke(SendCommandMessage $message)
    {
        $game = $this->gameRepository->findById($message->getId());
        if (null === $game) {
            throw new \Exception('Failed to send command');
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            throw new \Exception('Failed to send command');
        }

        $response = $this->connection->sendCommand($connection, $message->getCommand());
        if (false === $response) {
            $this->gameOperations->setStateAfterUpdateFailed($game);
            throw new \Exception('Failed to send command');
        } else {
            $this->gameOperations->setStateAfterUpdate($game);
        }
    }
}
