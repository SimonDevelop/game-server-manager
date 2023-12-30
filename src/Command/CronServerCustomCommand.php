<?php

namespace App\Command;

use App\Repository\GameServerRepository;
use App\Service\GameServerOperations;
use App\Service\Connection;
use App\Service\LogService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[AsCommand(
    name: 'cron:server:custom',
    description: 'Run the custom command of the game server',
)]
class CronServerCustomCommand extends Command
{
    public function __construct(
        private readonly GameServerRepository $gameServerRepository,
        private readonly GameServerOperations $gameOperations,
        private readonly Connection $connection,
        private readonly LogService $logService,
        private readonly TranslatorInterface $translator
    ) {
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
            $output->writeln($this->translator->trans('Game server not found'));

            return Command::FAILURE;
        }

        if (null === $game->getCommandCustomInternal()) {
            $output->writeln($this->translator->trans('No custom command set'));

            return Command::FAILURE;
        }

        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            $output->writeln($this->translator->trans('Failed to connect to server'));

            return Command::FAILURE;
        }

        $name = $this->gameOperations->getGameServerNameScreen($game);
        $cmd  = $game->getCommandCustomInternal();

        $command  = "screen -S $name -X stuff \"$cmd\"`echo -ne '\015'`";

        $output->writeln($this->translator->trans('Custom command sended!'));
        $response = $this->connection->sendCommand($connection, $command);
        sleep(10);

        if (false === $response) {
            $output->writeln($this->translator->trans('Failed to send custom command on game server'));
            $this->logService->addLog($game, $this->translator->trans('Failed to send custom command on game server'), true, null);

            return Command::FAILURE;
        }
        $this->logService->addLog($game, $this->translator->trans('Custom command sended!'), true, null);

        return Command::SUCCESS;
    }
}
