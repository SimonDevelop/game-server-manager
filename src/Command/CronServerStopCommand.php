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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'cron:server:stop',
    description: 'Run the stop command of the game server',
)]
class CronServerStopCommand extends Command
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
        $this->addOption(
            'kill',
            'kill',
            InputOption::VALUE_NONE,
            'Option for inject kill command'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id   = $input->getArgument('id');
        $game = $this->gameServerRepository->findById($id);

        if (null === $game) {
            $output->writeln('Game server not found');

            return Command::FAILURE;
        }

        if (null === $game->getCommandStop()) {
            $output->writeln('No stop command set');

            return Command::FAILURE;
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            $output->writeln('Failed to connect to server');

            return Command::FAILURE;
        }

        $game->setStateType(2);
        $this->em->persist($game);
        $this->em->flush();
        $name = $this->gameOperations->getGameServerNameScreen($game);
        $cmd  = $game->getCommandStop();
        
        if ($input->getOption('kill')) {
            $command  = "screen -XS $name quit";
        } else {
            $command  = "screen -S $name -X stuff \"$cmd\"`echo -ne '\015'`";
        }

        $output->writeln('Server stopping');
        $response = $this->connection->sendCommand($connection, $command);
        sleep(10);

        if (false === $response) {
            $output->writeln('Failed to stop game server');
            $game->setStateType(1);
            $this->em->persist($game);
            $this->em->flush();

            return Command::FAILURE;
        } else {
            $this->logService->addLog($game, 'Server stopped', true, null);
            $game->setStateType(0);
            $this->em->persist($game);
            $this->em->flush();
        }

        return Command::SUCCESS;
    }
}
