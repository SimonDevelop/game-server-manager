<?php

namespace App\Command;

use App\Repository\CronjobRepository;
use App\Repository\GameServerRepository;
use App\Service\GameServerOperations;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Connection;
use App\Service\LogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabRepository;

#[AsCommand(
    name: 'cron:server:check',
    description: 'Checks the status of the game servers and updates them if necessary.',
)]
class CronServerCheckCommand extends Command
{
    public function __construct(
        private readonly GameServerRepository $gameServerRepository,
        private readonly CronjobRepository $cronjobRepository,
        private readonly GameServerOperations $gameOperations,
        private readonly EntityManagerInterface $em,
        private readonly Connection $connection,
        private readonly LogService $logService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Checking game servers status
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
                    $this->logService->addLog($gameServer, 'Server updated to running', true, null);
                }

                if ($state === 'On' && !str_contains($logs, $name)) {
                    $output->writeln("$name is not running.");
                    $gameServer->setStateType(0);
                    $this->em->persist($gameServer);
                    $this->em->flush();
                    $this->logService->addLog($gameServer, 'Server updated to stopped', true, null);
                }
            }
        }

        // Checking cronjobs exists
        $cronjobs = $this->cronjobRepository->findAll();
        $crontabRepository = new CrontabRepository(new CrontabAdapter());
        foreach ($cronjobs as $cronjob) {
            $comment = $cronjob->getComment();
            $crontab = $crontabRepository->findJobByRegex("/$comment/");
            if (!isset($crontab[0])) {
                $action      = $cronjob->getType();
                $periodicity = $cronjob->getPeriodicity();
                $id          = $cronjob->getGameServer()->getId();
                $time        = '';
                if ("update" === $action) {
                    $time = '--time=120 ';
                }

                $crontabJob = CrontabJob::createFromCrontabLine("$periodicity php /app/bin/console cron:server:$action $id $time>> /var/log/cron.log 2>&1 #$comment");
                $crontabRepository->addJob($crontabJob);
                $crontabRepository->persist();
            }
        }

        return Command::SUCCESS;
    }
}
