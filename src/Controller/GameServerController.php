<?php

namespace App\Controller;

use App\Entity\GameServer;
use App\Form\GameServerType;
use App\Repository\GameServerRepository;
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
}
