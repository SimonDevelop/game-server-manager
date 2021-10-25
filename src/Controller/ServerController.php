<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\ServerType;
use App\Repository\ServerRepository;
use DivineOmega\SSHConnection\SSHConnection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Security("is_granted('ROLE_ADMIN')")
 * @Route("/server")
 */
class ServerController extends AbstractController
{
    /**
     * @Route("/", name="server_index", methods={"GET"})
     * @return Response
     */
    public function index(ServerRepository $serverRepository): Response
    {
        return $this->render('server/index.html.twig', [
            'servers' => $serverRepository->findAll(),
        ]);
    }

    /**
     * @Route("/new", name="server_new", methods={"GET", "POST"})
     * @return Response
     */
    public function new(Request $request): Response
    {
        $server = new Server();
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $server->getPassword()) {
                return $this->render('server/new.html.twig', [
                    'server' => $server,
                    'form' => $form->createView(),
                    'error_pass' => "Vous devez saisir un mot de passe."
                ]);
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($server);
            $entityManager->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/new.html.twig', [
            'server' => $server,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="server_edit", methods={"GET", "POST"})
     * @return Response
     */
    public function edit(Request $request, Server $server): Response
    {
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/edit.html.twig', [
            'server' => $server,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="server_delete", methods={"POST"})
     * @return Response
     */
    public function delete(Request $request, Server $server): Response
    {
        if ($this->isCsrfTokenValid('delete'.$server->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($server);
            $entityManager->flush();
        }

        return $this->redirectToRoute('server_index');
    }

    /**
     * @Route("/{id}/check", name="server_check", methods={"GET"})
     * @return Response
     */
    public function checkConnect(Request $request, Server $server): Response
    {
        try {
            (new SSHConnection())
                ->to($server->getIp())
                ->onPort($server->getPort())
                ->as($server->getLogin())
                ->withPassword($server->getPassword())
                ->connect();
            $this->addFlash('success', 'Authentification réussi !');
        } catch (\Throwable $th) {
            $this->addFlash('danger', 'Authentification échoué !');
        }

        return $this->redirectToRoute('server_index');
    }
}
