<?php

namespace App\Controller;

use App\Entity\GameServer;
use App\Form\GameServerType;
use App\Message\SendCommandMessage;
use App\Repository\GameServerRepository;
use App\Service\Connection;
use App\Service\GameServerOperations;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Messenger\MessageBusInterface;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")]
#[Route(path: '/game')]
class GameServerController extends AbstractController
{
    #@var GameServerRepository
    private $gameServerRepository;

    #@var GameServerOperations
    private $gameOperations;

    #@var EntityManagerInterface
    private $em;

    #@var MessageBusInterface
    private $messageBus;

    #@var Connection
    private $connection;

    #@param GameServerRepository
    #@param GameServerOperations
    #@param EntityManagerInterface
    #@param MessageBusInterface
    #@param Connection
    public function __construct(
        GameServerRepository $gameServerRepository,
        GameServerOperations $gameOperations,
        EntityManagerInterface $em,
        MessageBusInterface $messageBus,
        Connection $connection
    )
    {
        $this->gameServerRepository = $gameServerRepository;
        $this->gameOperations       = $gameOperations;
        $this->em                   = $em;
        $this->messageBus           = $messageBus;
        $this->connection           = $connection;   
    }

    #[Route(path: '/', name: 'game_index', methods: ['GET'])]
    public function index(): Response
    {
        $user = $this->getUser();
        if (in_array('ROLE_USER', $user->getRoles())) {
            $gameServers = $this->gameServerRepository->findByUser($user->getId());
        } else {
            $gameServers = $this->gameServerRepository->findAll();
        }

        return $this->render('game/index.html.twig', [
            'games' => $gameServers,
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN')")]
    #[Route(path: '/new', name: 'game_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $game = new GameServer();
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($game);
            $this->em->flush();
            $this->addFlash('success', 'Successful creation of the game server!');
            $sshResponse = $this->gameOperations->createLogConfig($game);
            if (false === $sshResponse) {
                $this->addFlash('danger', 'Log configuration failed!');
            }

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN')")]
    #[Route(path: '/{id}/edit', name: 'game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GameServer $game): Response
    {
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'Game server update successful!');
            $sshResponse = $this->gameOperations->createLogConfig($game);
            if (false === $sshResponse) {
                $this->addFlash('danger', 'Log configuration failed!');
            }

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Security("is_granted('ROLE_ADMIN')")]
    #[Route(path: '/{id}', name: 'game_delete', methods: ['POST'])]
    public function delete(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $this->em->remove($game);
            $this->em->flush();
            $this->addFlash('success', 'Successful suppression of the game server!');
        }

        return $this->redirectToRoute('game_index');
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
            $command  = "cd $path && touch server.log && screen -c $pathLogs -dmSL $name $cmd";
            $informations = [
                'user'   => $this->getUser()->getId(),
                'action' => 'Server started',
            ];

            $this->addFlash('success', 'Game server being launched!');
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
                'user'   => $this->getUser()->getId(),
                'action' => 'Server stopped',
            ];

            $this->addFlash('success', 'Game server being closed!');
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
                'user'   => $this->getUser()->getId(),
                'action' => 'Server Updating',
            ];

            $this->addFlash('success', 'Game server is being updated!');
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
                'user'   => $this->getUser()->getId(),
                'action' => 'Server killed',
            ];

            $this->addFlash('success', 'Game server being forced to close!');
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
        $command  = "cat $logsPath";
        $logs     = $this->connection->sendCommandWithResponse($connection, $command);

        return $this->render('game/logs.html.twig', [
            'game' => $game,
            'logs' => $logs
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
