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
        $events = [
            [
                'id' => 1,
                'nom' => 'Soirée Networking Lyon',
                'description' => 'Rencontrez les meilleurs professionnels du numérique autour d\'un cocktail dînatoire. Une soirée exceptionnelle pour développer votre réseau et échanger avec les acteurs clés de l\'innovation lyonnaise.',
                'date' => new \DateTime('+2 weeks'),
                'lieu' => 'Lyon 2e',
                'prix' => 29,
                'type' => 'Networking',
                'capacite' => 100,
                'heureDebut' => new \DateTime('19:00'),
                'reservations' => array_fill(0, 45, null), // 45 réservations
                'organisateur' => [
                    'nom' => 'Elysium Events',
                    'email' => 'contact@elysium-events.fr'
                ]
            ],
            [
                'id' => 2,
                'nom' => 'Workshop Symfony Avancé',
                'description' => 'Maîtrisez GraphQL et microservices avec Symfony 7. Formation intensive avec des cas pratiques et des experts reconnus du framework.',
                'date' => new \DateTime('+1 month'),
                'lieu' => 'Lyon 3e',
                'prix' => 89,
                'type' => 'Formation',
                'capacite' => 30,
                'heureDebut' => new \DateTime('09:00'),
                'reservations' => array_fill(0, 18, null),
                'organisateur' => [
                    'nom' => 'TechLyon Academy',
                    'email' => 'formation@techlyon.fr'
                ]
            ],
            [
                'id' => 3,
                'nom' => 'Nuit Électro Premium',
                'description' => 'Live DJ set dans un lieu mythique avec open bar. Une expérience musicale unique avec les meilleurs DJs de la scène électro française.',
                'date' => new \DateTime('+3 weeks'),
                'lieu' => 'Lyon 1er',
                'prix' => 45,
                'type' => 'Concert',
                'capacite' => 200,
                'heureDebut' => new \DateTime('22:00'),
                'reservations' => array_fill(0, 150, null),
                'organisateur' => [
                    'nom' => 'Lyon By Night',
                    'email' => 'contact@lyonbynight.fr'
                ]
            ],
            [
                'id' => 4,
                'nom' => 'Afterwork Startup',
                'description' => 'Pitchs innovants et networking entre fondateurs. Découvrez les startups les plus prometteuses de la région et échangez avec leurs créateurs.',
                'date' => new \DateTime('tomorrow'),
                'lieu' => 'Part-Dieu',
                'prix' => 15,
                'type' => 'Afterwork',
                'capacite' => 80,
                'heureDebut' => new \DateTime('18:30'),
                'reservations' => array_fill(0, 62, null),
                'organisateur' => [
                    'nom' => 'Startup Lyon',
                    'email' => 'hello@startuplyon.com'
                ]
            ],
            [
                'id' => 5,
                'nom' => 'Spectacle Art & Tech',
                'description' => 'Exposition immersive et dîner spectacle exclusif. Une soirée prestigieuse alliant art numérique, gastronomie et performance artistique.',
                'date' => new \DateTime('+1 week'),
                'lieu' => 'Confluence',
                'prix' => 125,
                'type' => 'Spectacle',
                'capacite' => 50,
                'heureDebut' => new \DateTime('19:30'),
                'reservations' => array_fill(0, 35, null),
                'organisateur' => [
                    'nom' => 'Confluence Culture',
                    'email' => 'events@confluence-culture.fr'
                ]
            ],
            [
                'id' => 6,
                'nom' => 'Hackathon 24h',
                'description' => 'Codez votre solution avec mentors experts. Relevez des défis techniques concrets et développez des projets innovants en équipe.',
                'date' => new \DateTime('+4 weeks'),
                'lieu' => 'Lyon 7e',
                'prix' => 0,
                'type' => 'Hackathon',
                'capacite' => 60,
                'heureDebut' => new \DateTime('09:00'),
                'reservations' => array_fill(0, 42, null),
                'organisateur' => [
                    'nom' => 'Code & Coffee',
                    'email' => 'hackathon@codecoffee.dev'
                ]
            ],
        ];

        $event = null;
        foreach ($events as $e) {
            if ($e['id'] == $id) {
                $event = $e;
                break;
            }
        }

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

}
