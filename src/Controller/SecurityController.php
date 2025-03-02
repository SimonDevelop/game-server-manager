<?php
namespace App\Controller;

use App\Form\AccountFormType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $em,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if (is_int($this->userRepository->countAdmin()) && 0 === $this->userRepository->countAdmin()) {
            return $this->redirectToRoute('app_setup');
        }

        $error        = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error'         => $error
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException($this->translator->trans('This method can be blank - it will be intercepted by the logout key on your firewall.'));
    }

    #[Route(path: '/account', name: 'app_account')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    public function account(Request $request, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $user = $this->userRepository->findOneBy([
            'username' => $this->getUser()->getUserIdentifier()
        ]);
        $form = $this->createForm(AccountFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (
                false === empty($form->get('password')->getData())
                || false === is_null($form->get('password')->getData())
            ) {
                $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('password')->getData()
                    )
                );
            }

            $this->em->persist($user);
            $this->em->flush();

            $this->addFlash('success', $this->translator->trans('Your account has been updated!'));

            return $this->redirectToRoute('app_account');
        }

        return $this->render('security/account.html.twig', [
            'user'        => $user,
            'accountForm' => $form->createView(),
        ]);
    }
}
