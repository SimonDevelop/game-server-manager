<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\ServerType;
use App\Repository\ServerRepository;
use App\Service\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_ADMIN')")]
#[Route(path: '/server')]
class ServerController extends AbstractController
{
    #@var ServerRepository
    private $serverRepository;

    #@var EntityManagerInterface
    private $em;

    #@param ServerRepository
    #@param EntityManagerInterface
    public function __construct(ServerRepository $serverRepository, EntityManagerInterface $em)
    {
        $this->serverRepository   = $serverRepository;
        $this->em                 = $em;
    }

    #[Route(path: '/', name: 'server_index', methods: ['GET'])]
    public function index(ServerRepository $serverRepository): Response
    {
        return $this->render('server/index.html.twig', [
            'servers' => $serverRepository->findAll(),
        ]);
    }

    #[Route(path: '/new', name: 'server_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $server = new Server();
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (null === $server->getPassword()) {
                return $this->render('server/new.html.twig', [
                    'server'     => $server,
                    'form'       => $form->createView(),
                    'error_pass' => "Vous devez saisir un mot de passe."
                ]);
            }
            $this->em->persist($server);
            $this->em->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/new.html.twig', [
            'server' => $server,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}/edit', name: 'server_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Server $server): Response
    {
        $form = $this->createForm(ServerType::class, $server);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();

            return $this->redirectToRoute('server_index');
        }

        return $this->render('server/edit.html.twig', [
            'server' => $server,
            'form' => $form->createView(),
        ]);
    }

    #[Route(path: '/{id}', name: 'server_delete', methods: ['POST'])]
    public function delete(Request $request, Server $server): Response
    {
        if ($this->isCsrfTokenValid('delete'.$server->getId(), $request->request->get('_token'))) {
            $this->em->remove($server);
            $this->em->flush();
        }

        return $this->redirectToRoute('server_index');
    }

    #[Route(path: '/{id}/check', name: 'server_check', methods: ['GET'])]
    public function checkConnect(Server $server, Connection $connexion): Response
    {
        if (null !== $connexion->getConnection($server)) {
            $this->addFlash('success', 'Authentification réussi !');
        } else {
            $this->addFlash('danger', 'Authentification échoué !');
        }

        return $this->redirectToRoute('server_index');
    }
}
