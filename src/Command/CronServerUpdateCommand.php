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
    name: 'cron:server:update',
    description: 'Run the update command then restart the server if it is on at the beginning',
)]
class CronServerUpdateCommand extends Command
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
            'time',
            'time',
            InputOption::VALUE_OPTIONAL,
            'time of game server restart after update (in seconds)',
            120
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id   = $input->getArgument('id');
        $time = $input->getOption('time');
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

        $needStart = false;
        if ($game->getState() === 'On') {
            $game->setStateType(2);
            $this->em->persist($game);
            $this->em->flush();

            $name     = $this->gameOperations->getGameServerNameScreen($game);
            $cmd      = $game->getCommandStop();
            $command  = "screen -S $name -X stuff \"$cmd\"`echo -ne '\015'`";
            
            $output->writeln('Server stopping');
            $response = $this->connection->sendCommand($connection, $command);
            sleep(10);

            if (false === $response) {
                $output->writeln("Failed to stop game server ($name)");
                $game->setStateType(1);
                $this->em->persist($game);
                $this->em->flush();

                return Command::FAILURE;
            } else {
                $needStart = true;
                $this->logService->addLog($game, 'Server stopped', true, null);
            }
        }

        $game->setStateType(4);
        $this->em->persist($game);
        $this->em->flush();

        $cmd      = $game->getCommandUpdate();
        $path     = $game->getPath();
        $command  = "cd $path && $cmd";
        $output->writeln('Server updating');
        $response = $this->connection->sendCommand($connection, $command);
        sleep($time);

        if (false === $response) {
            $output->writeln("Failed to update game server ($name)");
            $game->setStateType(0);
            $this->em->persist($game);
            $this->em->flush();

            return Command::FAILURE;
        } else {
            $output->writeln('Server updated');
            $this->logService->addLog($game, 'Server updated', true, null);
            if (false === $needStart) {
                $game->setStateType(0);
                $this->em->persist($game);
                $this->em->flush();
            }
        }

        if ($needStart) {
            $game->setStateType(3);
            $this->em->persist($game);
            $this->em->flush();

            $name     = $this->gameOperations->getGameServerNameScreen($game);
            $path     = $game->getPath();
            $pathLogs = $this->gameOperations->getGameServerLogConf($game);
            $cmd      = $game->getCommandStart();
            $command  = "cd $path && touch server.log && screen -c $pathLogs -dmSL $name $cmd";

            $output->writeln('Server starting');
            $response = $this->connection->sendCommand($connection, $command);
            sleep(10);

            if (false === $response) {
                $output->writeln("Failed to start game server ($name)");
                $game->setStateType(0);
                $this->em->persist($game);
                $this->em->flush();

                return Command::FAILURE;
            } else {
                $output->writeln('Server started');
                $this->logService->addLog($game, 'Server started', true, null);
                $game->setStateType(1);
                $this->em->persist($game);
                $this->em->flush();
            }
        }

        return Command::SUCCESS;
    }
}
