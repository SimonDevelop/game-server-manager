<?php

namespace App\Controller;

use App\Entity\GameServer;
use App\Form\GameServerType;
use App\Message\SendCommand;
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

#[Security("is_granted('ROLE_ADMIN')")]
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

    #[Route(path: '/game', name: 'game_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $this->gameServerRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'game_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $game = new GameServer();
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($game);
            $this->em->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'game_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, GameServer $game): Response
    {
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'game_delete', methods: ['POST'])]
    public function delete(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $this->em->remove($game);
            $this->em->flush();
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

            $this->messageBus->dispatch(new SendCommand($game->getServer()->getId(), $command));
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

            $this->messageBus->dispatch(new SendCommand($game->getServer()->getId(), $command));
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
            $this->messageBus->dispatch(new SendCommand($game->getServer()->getId(), $command));
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
}
