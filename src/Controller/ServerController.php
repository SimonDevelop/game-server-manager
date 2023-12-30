<?php

namespace App\Controller;

use App\Entity\Server;
use App\Form\ServerType;
use App\Repository\ServerRepository;
use App\Service\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted(new Expression('is_granted("ROLE_ADMIN")'))]
#[Route(path: '/server')]
class ServerController extends AbstractController
{
    public function __construct(
        private readonly ServerRepository $serverRepository,
        private readonly EntityManagerInterface $em,
        private readonly Connection $connection,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/', name: 'server_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('server/index.html.twig', [
            'servers' => $this->serverRepository->findAll(),
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
                    'error_pass' => $this->translator->trans('You must enter a password')
                ]);
            }
            $this->em->persist($server);
            $this->em->flush();
            $this->addFlash('success', $this->translator->trans('Successful server creation!'));

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
            $this->addFlash('success', $this->translator->trans('Successful server update!'));

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
            $this->addFlash('success', $this->translator->trans('Successful server suppression!'));
        }

        return $this->redirectToRoute('server_index');
    }

    #[Route(path: '/{id}/check', name: 'server_check', methods: ['GET'])]
    public function checkConnect(Server $server): Response
    {
        if (true === $this->connection->getConnection($server)) {
            $this->addFlash('success', $this->translator->trans('Authentication successful!'));
        } else {
            $this->addFlash('danger', $this->translator->trans('Authentication failed!'));
        }

        return $this->redirectToRoute('server_index');
    }
}
