<?php

namespace App\MessageHandler;

use App\Message\SendCommandMessage;
use App\Repository\GameServerRepository;
use App\Repository\UserRepository;
use App\Service\Connection;
use App\Service\GameServerOperations;
use App\Service\LogService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class SendCommandHandler
{
    /**
     * @var GameServerRepository
     */
    private $gameRepository;

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var GameServerOperations
     */
    private $gameOperations;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var LogService
     */
    private $logService;

    #@param GameServerRepository
    #@param UserRepository
    #@param GameServerOperations
    #@param Connection
    #@param LogService
    public function __construct(
        GameServerRepository $gameRepository,
        UserRepository $userRepository,
        GameServerOperations $gameOperations,
        Connection $connection,
        LogService $logService
    )
    {
        $this->gameRepository = $gameRepository;
        $this->userRepository = $userRepository;
        $this->gameOperations = $gameOperations;
        $this->connection     = $connection;
        $this->logService     = $logService;   
    }

    public function __invoke(SendCommandMessage $message)
    {
        $game = $this->gameRepository->findById($message->getId());
        if (null === $game) {
            throw new \Exception('Failed to send command');
        }

        $informations = $message->getInformations();
        $user = $this->userRepository->find($informations['user']);
        if (null === $user) {
            throw new \Exception('Failed to send command');
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            $this->logService->addLog($user, $game, $informations['action'], false);
            throw new \Exception('Failed to send command');
        }

        $response = $this->connection->sendCommand($connection, $message->getCommand());
        if (false === $response) {
            $this->gameOperations->setStateAfterUpdateFailed($game);
            $this->logService->addLog($user, $game, $informations['action'], false);
            throw new \Exception('Failed to send command');
        } else {
            $this->gameOperations->setStateAfterUpdate($game);
            $this->logService->addLog($user, $game, $informations['action']);
        }
    }
}
