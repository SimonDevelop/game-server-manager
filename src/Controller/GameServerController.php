<?php

namespace App\Controller;

use App\Entity\GameServer;
use App\Form\GameServerType;
use App\Message\SendCommand;
use App\Repository\GameServerRepository;
use App\Service\GameServerOperations;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("/game")
 */
class GameServerController extends AbstractController
{
    /**
     * @Route("/", name="game_index", methods={"GET"})
     * @return Response
     */
    public function index(GameServerRepository $gameServerRepository): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $gameServerRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="game_new", methods={"GET", "POST"})
     * @return Response
     */
    public function new(Request $request): Response
    {
        $game = new GameServer();
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/new.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="game_edit", methods={"GET", "POST"})
     * @return Response
     */
    public function edit(Request $request, GameServer $game): Response
    {
        $form = $this->createForm(GameServerType::class, $game);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('game_index');
        }

        return $this->render('game/edit.html.twig', [
            'game' => $game,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="game_delete", methods={"POST"})
     * @return Response
     */
    public function delete(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('delete'.$game->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('game_index');
    }

    /**
     * @Route("/{id}/on", name="game_on", methods={"POST"})
     * @return Response
     */
    public function gameOn(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('on'.$game->getId(), $request->request->get('_token'))) {
            $name    = GameServerOperations::getGameServerNameScreen($game);
            $path    = $game->getPath();
            $cmd     = $game->getCommandStart();
            $command = "cd $path && screen -d -m -S $name $cmd";
            $this->dispatchMessage(new SendCommand(1, $command));

            $entityManager = $this->getDoctrine()->getManager();
            $game->setStateType(1);
            $entityManager->persist($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('game_index');
    }

    /**
     * @Route("/{id}/off", name="game_off", methods={"POST"})
     * @return Response
     */
    public function gameOff(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('off'.$game->getId(), $request->request->get('_token'))) {
            $name    = GameServerOperations::getGameServerNameScreen($game);
            $cmd     = $game->getCommandStop();
            $command = "screen -S $name -X stuff \"$cmd\"";
            $this->dispatchMessage(new SendCommand(1, $command));

            $entityManager = $this->getDoctrine()->getManager();
            $game->setStateType(0);
            $entityManager->persist($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('game_index');
    }

    /**
     * @Route("/{id}/kill", name="game_kill", methods={"POST"})
     * @return Response
     */
    public function gameKill(Request $request, GameServer $game): Response
    {
        if ($this->isCsrfTokenValid('kill'.$game->getId(), $request->request->get('_token'))) {
            $name    = GameServerOperations::getGameServerNameScreen($game);
            $command = "screen -XS $name quit";
            $this->dispatchMessage(new SendCommand(1, $command));

            $entityManager = $this->getDoctrine()->getManager();
            $game->setStateType(0);
            $entityManager->persist($game);
            $entityManager->flush();
        }

        return $this->redirectToRoute('game_index');
    }
}
