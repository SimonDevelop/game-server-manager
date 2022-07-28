<?php

namespace App\MessageHandler;

use App\Message\SendCommand;
use App\Repository\GameServerRepository;
use App\Service\Connection;
use App\Service\GameServerOperations;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

class SendCommandHandler implements MessageHandlerInterface
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

    public function __invoke(SendCommand $message)
    {
        $game = $this->gameRepository->findById($message->getId());
        if (null === $game) {
            return 0;
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            return 0;
        }

        $this->connection->sendCommand($connection, $message->getCommand());
        $this->gameOperations->setStateAfterUpdate($game);
    }
}
