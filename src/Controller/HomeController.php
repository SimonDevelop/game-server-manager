<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USER')")]
class HomeController extends AbstractController
{
    #[Route(path: '/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render("pages/home.html.twig");
    }
}
