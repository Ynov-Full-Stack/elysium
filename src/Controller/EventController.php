<?php

namespace App\Controller;

use App\Entity\Event;
use App\Repository\EventRepository;
use Nzo\UrlEncryptorBundle\Annotations\ParamDecryptor;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/event')]
final class EventController extends AbstractController
{
    #[Route('/', name: 'app_event_index')]
    public function index(EventRepository $eventRepository, Request $request): Response
    {
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 12;
        $data = $eventRepository->findFilteredPaginated($request, $page, $limit);
        $cities = $eventRepository->findDistinctLieux();
        $types = $eventRepository->findDistinctTypes();

        return $this->render('pages/event/index.html.twig', [
            'events' => $data['events'],
            'pagination' => [
                'currentPage' => $data['currentPage'],
                'totalPages' => $data['totalPages'],
                'totalItems' => $data['totalItems'],
                'itemsPerPage' => $limit
            ],
            'cities' => $cities,
            'types' => $types
        ]);
    }

    #[Route('/show/{id}', name: 'app_event_show')]
    #[ParamDecryptor(['id'])]
    public function show(EventRepository $eventRepository, $id, Request $request): Response
    {
        $event = $eventRepository->findOneBy(['id' => $id]);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé');
        }


        return $this->render('pages/event/show.html.twig', [
            'event' => $event,
            'previousUrl' => $request->headers->get('referer'),
        ]);
    }

}
