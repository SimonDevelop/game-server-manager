<?php

namespace App\Controller;

use App\Entity\Cronjob;
use App\Entity\GameServer;
use App\Form\CronType;
use App\Form\GameServerType;
use App\Message\SendCommandMessage;
use App\Repository\CronjobRepository;
use App\Repository\GameServerRepository;
use App\Service\Connection;
use App\Service\GameServerOperations;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use TiBeN\CrontabManager\CrontabAdapter;
use TiBeN\CrontabManager\CrontabJob;
use TiBeN\CrontabManager\CrontabRepository;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN") or is_granted("ROLE_USER")'))]
#[Route(path: '/game')]
class GameServerController extends AbstractController
{
    public function __construct(
        private readonly GameServerRepository $gameServerRepository,
        private readonly GameServerOperations $gameOperations,
        private readonly EntityManagerInterface $em,
        private readonly MessageBusInterface $messageBus,
        private readonly Connection $connection,
        private readonly LoggerInterface $logger,
        private readonly CronjobRepository $cronjobRepository,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/', name: 'game_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if (in_array('ROLE_USER', $user->getRoles())) {
            $gameServers = $this->gameServerRepository->findByUsername($user->getUserIdentifier());
        } else {
            $gameServers = $this->gameServerRepository->findAllWithOrder();
        }

        return $this->render('game/index.html.twig', [
            'games' => $gameServers,
        ]);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    #[Route(path: '/new', name: 'game_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $game = new GameServer();
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($game);
            $this->em->flush();
            $this->addFlash('success', $this->translator->trans('Successful creation of the game server!'));
            $sshResponse = $this->gameOperations->createLogConfig($game);
            if (false === $sshResponse) {
                $this->addFlash('danger', $this->translator->trans('Log configuration failed!'));
            }

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    #[Route(path: '/{id}/edit', name: 'game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GameServer $game): Response
    {
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', $this->translator->trans('Game server update successful!'));
            $sshResponse = $this->gameOperations->createLogConfig($game);
            if (false === $sshResponse) {
                $this->addFlash('danger', $this->translator->trans('Log configuration failed!'));
            }

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    #[Route(path: '/{id}', name: 'game_delete', methods: ['POST'])]
    public function delete(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $this->em->remove($game);
            $this->em->flush();
            $this->addFlash('success', $this->translator->trans('Successful suppression of the game server!'));
        }

        return $this->redirectToRoute('game_index');
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    #[Route(path: '/{id}/cron', name: 'game_crons', methods: ['GET', 'POST'])]
    public function cron(Request $request, GameServer $game): Response
    {
        $crontabRepository = new CrontabRepository(new CrontabAdapter());
        $name              = $this->gameOperations->getGameServerNameScreen($game);
        $id                = $game->getId();
        $crons             = [
            "start" => $crontabRepository->findJobByRegex("/".$name."_start_/"),
            "stop" => $crontabRepository->findJobByRegex("/".$name."_stop_/"),
            "update" => $crontabRepository->findJobByRegex("/".$name."_update_/"),
            "custom" => $crontabRepository->findJobByRegex("/".$name."_custom_/")
        ];

        $form = $this->createForm(CronType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $datas       = $request->getPayload()->all();
            $action      = $datas['cron']['command'];
            $periodicity = $datas['cron']['periodicity'];

            $time = '';
            if ("update" === $action) {
                $time = '--time=120 ';
            }

            try {
                $crontabJob = CrontabJob::createFromCrontabLine("$periodicity php /app/bin/console cron:server:$action $id $time>> /var/log/cron.log 2>&1");
                $crontabJob->setComments($name."_".$action."_".(count($crons[$action])+1));
                $crontabRepository->addJob($crontabJob);
                $crontabRepository->persist();
                $cronjobEntity = new Cronjob();
                $cronjobEntity->setType($action);
                $cronjobEntity->setPeriodicity($periodicity);
                $cronjobEntity->setComment($crontabJob->getComments());
                $cronjobEntity->setGameServer($game);
                $this->em->persist($cronjobEntity);
                $this->em->flush();

                $this->addFlash('success', $this->translator->trans('Cronjob created with successful!'));
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());

                $this->addFlash('danger', $this->translator->trans('Cronjob created failed!'));
            }

            return $this->redirectToRoute('game_crons', [
                'id' => $id
            ]);
        }

        return $this->render('game/crons.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
            'crons' => $crons
        ]);
    }

    #[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
    #[Route(path: '/{id}/cron/delete', name: 'game_cron_delete', methods: ['POST'])]
    public function cronDelete(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('cron'.$game->getId(), $request->request->get('_token'))) {
            try {
                $cronjob = $request->getPayload()->get('cronjob');
                $crontabRepository = new CrontabRepository(new CrontabAdapter());
                $crontabJob = $crontabRepository->findJobByRegex("/$cronjob/");
                $crontabRepository->removeJob($crontabJob[0]);
                $crontabRepository->persist();
                $cronjobEntity = $this->cronjobRepository->findOneBy([
                    'comment' => $cronjob
                ]);

                if (null !== $cronjobEntity) {
                    $this->em->remove($cronjobEntity);
                    $this->em->flush();
                }

                $this->addFlash('success', $this->translator->trans('The cronjob has been deleted!'));
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                $this->addFlash('danger', $this->translator->trans('The cronjob could not be deleted!'));
            }
        }

        return $this->redirectToRoute('game_crons', [
            'id' => $game->getId()
        ]);
    }

    #[Route(path: '/{id}/on', name: 'game_on', methods: ['POST'])]
    public function gameOn(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('on'.$game->getId(), $request->request->get('_token'))) {
            $game->setStateType(3);
            $this->em->persist($game);
            $this->em->flush();

            $name     = $this->gameOperations->getGameServerNameScreen($game);
            $path     = $game->getPath();
            $pathLogs = $this->gameOperations->getGameServerLogConf($game);
            $cmd      = $game->getCommandStart();
            if ($game->getSudoer()) {
                $cmd = 'sudo '.$cmd;
            }
            $command = "cd $path && touch server.log && screen -c $pathLogs -dmSL $name $cmd";
            $informations = [
                'user'   => $this->getUser()->getUserIdentifier(),
                'action' => 'Server started',
            ];

            $this->addFlash('success', $this->translator->trans('Game server being launched!'));
            $this->messageBus->dispatch(new SendCommandMessage($game->getId(), $informations, $command));
        }

        return $this->redirectToRoute('game_index');
    }

    #[Route(path: '/{id}/off', name: 'game_off', methods: ['POST'])]
    public function gameOff(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('off'.$game->getId(), $request->request->get('_token'))) {
            $game->setStateType(2);
            $this->em->persist($game);
            $this->em->flush();

            $name    = $this->gameOperations->getGameServerNameScreen($game);
            $cmd     = $game->getCommandStop();
            $command = "screen -S $name -X stuff \"$cmd\"`echo -ne '\015'`";
            $informations = [
                'user'   => $this->getUser()->getUserIdentifier(),
                'action' => 'Server stopped',
            ];

            $this->addFlash('success', $this->translator->trans('Game server being closed!'));
            $this->messageBus->dispatch(new SendCommandMessage($game->getId(), $informations, $command));
        }

        return $this->redirectToRoute('game_index');
    }

    #[Route(path: '/{id}/update', name: 'game_update', methods: ['POST'])]
    public function gameUpdate(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('update'.$game->getId(), $request->request->get('_token'))) {
            $game->setStateType(4);
            $this->em->persist($game);
            $this->em->flush();

            $path    = $game->getPath();
            $cmd     = $game->getCommandUpdate();
            $command = "cd $path && $cmd";
            $informations = [
                'user'   => $this->getUser()->getUserIdentifier(),
                'action' => 'Server Updating',
            ];

            $this->addFlash('success', $this->translator->trans('Game server is being updated!'));
            $this->messageBus->dispatch(new SendCommandMessage($game->getId(), $informations, $command));
        }

        return $this->redirectToRoute('game_index');
    }

    #[Route(path: '/{id}/kill', name: 'game_kill', methods: ['POST'])]
    public function gameKill(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('kill'.$game->getId(), $request->request->get('_token'))) {
            $game->setStateType(2);
            $this->em->persist($game);
            $this->em->flush();

            $name    = $this->gameOperations->getGameServerNameScreen($game);
            $command = "screen -XS $name quit";
            $informations = [
                'user'   => $this->getUser()->getUserIdentifier(),
                'action' => 'Server killed',
            ];

            $this->addFlash('success', $this->translator->trans('Game server being forced to close!'));
            $this->messageBus->dispatch(new SendCommandMessage($game->getId(), $informations, $command));
        }

        return $this->redirectToRoute('game_index');
    }

    #[Route(path: '/{id}/logs', name: 'game_logs', methods: ['GET'])]
    public function gameLog(GameServer $game): Response
    {
        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            return $this->redirectToRoute('game_index');
        }

        $logsPath = $this->gameOperations->getGameServerLog($game);
        $command  = "tail --lines=100 $logsPath";
        $logs     = $this->connection->sendCommandWithResponse($connection, $command);

        return $this->render('game/logs.html.twig', [
            'game' => $game,
            'logs' => $logs
        ]);
    }

    #[Route(path: '/{id}/cmd', name: 'game_cmd', methods: ['POST'])]
    public function gameCmd(GameServer $game, Request $request): Response
    {
        if ($this->isCsrfTokenValid('cmd'.$game->getId(), $request->request->get('_token'))) {
            try {
                $cmd  = $request->getPayload()->get('cmd');
                $name = $this->gameOperations->getGameServerNameScreen($game);
                $command = "screen -S $name -X stuff \"$cmd\"`echo -ne '\015'`";
                $connection = $this->connection->getConnection($game->getServer());
                if (null === $connection) {
                    return $this->redirectToRoute('game_index');
                }

                $this->connection->sendCommand($connection, $command);
                $this->addFlash('success', $this->translator->trans('Command sended!'));
            } catch (\Exception $exception) {
                $this->logger->error($exception->getMessage());
                $this->addFlash('danger', $this->translator->trans('Command send failed!'));
            }
        }

        return $this->redirectToRoute('game_logs', [
            'id' => $game->getId()
        ]);
    }

    #[Route(path: '/{id}/logs/clear', name: 'game_logs_clear', methods: ['GET'])]
    public function gameLogClear(GameServer $game): Response
    {
        $connection = $this->connection->getConnection($game->getServer());
        if (null === $connection) {
            return $this->redirectToRoute('game_index');
        }

        $logsPath = $this->gameOperations->getGameServerLog($game);
        $command  = "echo '' > $logsPath";
        $this->connection->sendCommand($connection, $command);

        return $this->redirectToRoute('game_logs', [
            'id' => $game->getId()
        ]);
    }
}
