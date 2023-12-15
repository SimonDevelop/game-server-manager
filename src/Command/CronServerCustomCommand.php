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
    name: 'cron:server:custom',
    description: 'Run the custom command of the game server',
)]
class CronServerCustomCommand extends Command
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

        if (null === $game->getCommandCustomInternal()) {
            $output->writeln('No custom command set');

            return Command::FAILURE;
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            $output->writeln('Failed to connect to server');

            return Command::FAILURE;
        }

        $name = $this->gameOperations->getGameServerNameScreen($game);
        $cmd  = $game->getCommandCustomInternal();

        $command  = "screen -S $name -X stuff \"$cmd\"`echo -ne '\015'`";

        $output->writeln('Custom command sended!');
        $response = $this->connection->sendCommand($connection, $command);
        sleep(10);

        if (false === $response) {
            $output->writeln('Failed to send custom command on game server');

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
