<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        return $this->render('pages/home/index.html.twig');
    }

    #[Route('/legal_notices', name: 'app_legal_notices')]
    public function legal_notices(): Response
    {
        return $this->render('components/legal_notices.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('components/about.html.twig');
    }

}
