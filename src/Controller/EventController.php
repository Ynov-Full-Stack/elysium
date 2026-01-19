<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index')]
    public function index(): Response
    {
        $events = [
            ['id' => 1, 'nom' => 'Soirée Networking Lyon', 'description' => 'Rencontrez les meilleurs professionnels du numérique autour d\'un cocktail dînatoire.', 'date' => new \DateTime('+2 weeks'), 'lieu' => 'Lyon 2e', 'prix' => 29, 'type' => 'Networking'],
            ['id' => 2, 'nom' => 'Workshop Symfony Avancé', 'description' => 'Maîtrisez GraphQL et microservices avec Symfony 7.', 'date' => new \DateTime('+1 month'), 'lieu' => 'Lyon 3e', 'prix' => 89, 'type' => 'Formation'],
            ['id' => 3, 'nom' => 'Concert Électro Premium', 'description' => 'Live DJ set dans un lieu mythique avec open bar.', 'date' => new \DateTime('+3 weeks'), 'lieu' => 'Lyon 1er', 'prix' => 45, 'type' => 'Concert'],
            ['id' => 4, 'nom' => 'Afterwork Startup', 'description' => 'Pitchs innovants et networking entre fondateurs.', 'date' => new \DateTime('tomorrow'), 'lieu' => 'Part-Dieu', 'prix' => 15, 'type' => 'Afterwork'],
            ['id' => 5, 'nom' => 'Gala Art & Tech', 'description' => 'Exposition immersive et dîner spectacle exclusif.', 'date' => new \DateTime('+1 week'), 'lieu' => 'Confluence', 'prix' => 125, 'type' => 'Gala'],
            ['id' => 6, 'nom' => 'Hackathon 24h', 'description' => 'Codez votre solution avec mentors experts.', 'date' => new \DateTime('+4 weeks'), 'lieu' => 'Lyon 7e', 'prix' => 0, 'type' => 'Hackathon'],
        ];

        return $this->render('event/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/show/{id}', name: 'app_event_show')]
    public function show($id): Response
    {
        return $this->render('event/show.html.twig', [

        ]);
    }

}
