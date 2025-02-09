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
    public function __construct(
        private readonly GameServerRepository $gameRepository,
        private readonly UserRepository $userRepository,
        private readonly GameServerOperations $gameOperations,
        private readonly Connection $connection,
        private readonly LogService $logService
    ) {
    }

    public function __invoke(SendCommandMessage $message): void
    {
        $game = $this->gameRepository->findById($message->getId());
        if (null === $game) {
            throw new \Exception('Failed to send command (game server not exist)');
        }

        $informations = $message->getInformations();
        $user = $this->userRepository->findOneBy(['username' => $informations['user']]);
        if (null === $user) {
            throw new \Exception('Failed to send command (user not exist)');
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            $this->logService->addLog($game, $informations['action'], false, $user);
            throw new \Exception('Failed to send command (failed to get server connection)');
        }

        $response = $this->connection->sendCommand($connection, $message->getCommand());
        if (false === $response) {
            $this->gameOperations->setStateAfterUpdateFailed($game);
            $this->logService->addLog($game, $informations['action'], false, $user);
            throw new \Exception('Failed to send command (operation command)');
        } else {
            $this->gameOperations->setStateAfterUpdate($game);
            $this->logService->addLog($game, $informations['action'], true, $user);
        }
    }
}
