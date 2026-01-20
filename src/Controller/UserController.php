<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/user')]
class UserController extends AbstractController
{
    #[Route('/', name: 'app_user_index')]
    public function index(): Response
    {
        $stats = [
            'upcoming' => 5,
            'past' => 12,
            'total_spent' => 850,
            'points' => 420
        ];

        $upcomingReservations = [
            [
                'event' => [
                    'id' => 3,
                    'nom' => 'Nuit Électro Premium',
                    'type' => 'Concert',
                    'date' => new \DateTime('+3 weeks'),
                    'lieu' => 'Lyon 1er',
                    'prix' => 45
                ],
                'nombrePlaces' => 2
            ],
            [
                'event' => [
                    'id' => 2,
                    'nom' => 'Workshop Symfony Avancé',
                    'type' => 'Formation',
                    'date' => new \DateTime('+1 month'),
                    'lieu' => 'Lyon 3e',
                    'prix' => 89
                ],
                'nombrePlaces' => 1
            ],
            [
                'event' => [
                    'id' => 4,
                    'nom' => 'Afterwork Startup',
                    'type' => 'Afterwork',
                    'date' => new \DateTime('tomorrow'),
                    'lieu' => 'Part-Dieu',
                    'prix' => 15
                ],
                'nombrePlaces' => 1
            ]
        ];

        return $this->render('pages/user/index.html.twig', [
            'stats' => $stats,
            'upcoming_reservations' => $upcomingReservations
        ]);
    }

    #[Route('/profil', name: 'app_user_profile')]
    public function profile(): Response
    {
        return $this->render('pages/user/profile.html.twig');
    }

    #[Route('/reservations', name: 'app_user_reservations')]
    public function reservations(): Response
    {
        // Simulation de toutes les réservations
        $reservations = [
            [
                'event' => [
                    'nom' => 'Nuit Électro Premium',
                    'type' => 'Concert',
                    'date' => new \DateTime('+3 weeks'),
                    'lieu' => 'Lyon 1er'
                ],
                'nombrePlaces' => 2
            ],
            [
                'event' => [
                    'nom' => 'Workshop Symfony Avancé',
                    'type' => 'Formation',
                    'date' => new \DateTime('+1 month'),
                    'lieu' => 'Lyon 3e'
                ],
                'nombrePlaces' => 1
            ],
            [
                'event' => [
                    'nom' => 'Spectacle Art & Tech',
                    'type' => 'Spectacle',
                    'date' => new \DateTime('-1 week'),
                    'lieu' => 'Confluence'
                ],
                'nombrePlaces' => 1
            ]
        ];

        $pagination = [
            'currentPage' => 1,
            'totalPages' => 1
        ];

        return $this->render('pages/user/reservations.html.twig', [
            'reservations' => $reservations,
            'pagination' => $pagination
        ]);
    }

    #[Route('/preferences', name: 'app_user_preferences')]
    public function preferences(): Response
    {
        return $this->render('pages/user/preferences.html.twig');
    }
}
