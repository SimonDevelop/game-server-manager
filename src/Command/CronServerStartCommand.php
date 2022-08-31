<?php

namespace App\Command;

use App\Repository\GameServerRepository;
use App\Service\GameServerOperations;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Connection;
use App\Service\LogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'cron:server:start',
    description: 'Run the start command of the game server',
)]
class CronServerStartCommand extends Command
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

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'id of game server');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id   = $input->getArgument('id');
        $game = $this->gameServerRepository->findById($id);

        if (null === $game) {
            $output->writeln('Game server not found');

            return Command::FAILURE;
        }

        if (null === $game->getCommandUpdate()) {
            $output->writeln('No update command set');

            return Command::FAILURE;
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            $output->writeln('Failed to connect to server');

            return Command::FAILURE;
        }

        $game->setStateType(3);
        $this->em->persist($game);
        $this->em->flush();
        $name     = $this->gameOperations->getGameServerNameScreen($game);
        $path     = $game->getPath();
        $pathLogs = $this->gameOperations->getGameServerLogConf($game);
        $cmd      = $game->getCommandStart();
        $command  = "cd $path && touch server.log && screen -c $pathLogs -dmSL $name $cmd";
        $response = $this->connection->sendCommand($connection, $command);
        if (false === $response) {
            $output->writeln('Failed to start game server');

            return Command::FAILURE;
        } else {
            sleep(10);
            $this->logService->addLog(null, $game, 'Server started');
            $game->setStateType(1);
            $this->em->persist($game);
            $this->em->flush();
        }

        return Command::SUCCESS;
    }
}
