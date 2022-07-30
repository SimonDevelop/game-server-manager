<?php

namespace App\Command;

use App\Repository\GameServerRepository;
use App\Service\GameServerOperations;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Connection;
use App\Service\LogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'cron:server:check',
    description: 'Checks the status of the game servers and updates them if necessary.',
)]
class CronServerCheckCommand extends Command
{
    #@var GameServerRepository
    private $gameServerRepository;

    #@var GameServerOperations
    private $gameOperations;

    #@var EntityManagerInterface
    private $em;

    #@var Connection
    private $connection;

    #@var LogService
    private $logService;

    #@param GameServerRepository
    #@param GameServerOperations
    #@param EntityManagerInterface
    #@param Connection
    public function __construct(
        GameServerRepository $gameServerRepository,
        GameServerOperations $gameOperations,
        EntityManagerInterface $em,
        Connection $connection,
        LogService $logService
    )
    {
        $this->gameServerRepository = $gameServerRepository;
        $this->gameOperations       = $gameOperations;
        $this->em                   = $em;
        $this->connection           = $connection;
        $this->logService           = $logService;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $gameServers = $this->gameServerRepository->findAll();
        foreach ($gameServers as $gameServer) {
            $connection = $this->connection->getConnection($gameServer->getServer());
            if (null !== $connection) {
                $state   = $gameServer->getState();
                $name    = $this->gameOperations->getGameServerNameScreen($gameServer);
                $command = "screen -ls | grep -i \"{$name}\"";
                $logs    = $this->connection->sendCommandWithResponse($connection, $command);

                if ($state === 'Off' && str_contains($logs, $name)) {
                    $output->writeln("$name is running.");
                    $gameServer->setStateType(1);
                    $this->em->persist($gameServer);
                    $this->em->flush();
                    $this->logService->addLog(null, $gameServer, 'Server updated to running');
                }

                if ($state === 'On' && !str_contains($logs, $name)) {
                    $output->writeln("$name is not running.");
                    $gameServer->setStateType(0);
                    $this->em->persist($gameServer);
                    $this->em->flush();
                    $this->logService->addLog(null, $gameServer, 'Server updated to stopped');
                }
            }
        }

        return Command::SUCCESS;
    }
}
