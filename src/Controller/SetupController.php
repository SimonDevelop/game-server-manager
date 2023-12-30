<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\SetupFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class SetupController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/setup', name: 'app_setup', methods: ['GET', 'POST'])]
    public function index(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        if (is_int($this->userRepository->countAdmin()) && 0 < $this->userRepository->countAdmin()) {
            return $this->redirectToRoute('app_login');
        }

        $user = new User();
        $form = $this->createForm(SetupFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (null === $user->getPassword()) {
                return $this->render('setup/index.html.twig', [
                    'user'       => $user,
                    'form'       => $form->createView(),
                    'error_pass' => $this->translator->trans('You must enter a password')
                ]);
            }
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('password')->getData()
                )
            );
            $user->setRoles(['ROLE_ADMIN']);
            $user->setEnabled(true);

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Your admin account has been created!'));

            return $this->redirectToRoute('app_login');
        }

        return $this->render('setup/index.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
