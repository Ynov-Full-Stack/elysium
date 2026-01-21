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
    public function index(EventRepository $eventRepository): Response
    {
        // add condition for show just futur event, and route condition with type
        $events = $eventRepository->findAll();

        return $this->render('pages/event/index.html.twig', [
            'events' => $events,
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

        // remaining seat
        $reservedSeats = 0;
        foreach ($event->getReservations() as $reservation) {
            $reservedSeats += $reservation->getSeatQuantity();
        }

        $remainingSeat = $event->getTotalSeats() - $reservedSeats;


        return $this->render('pages/event/show.html.twig', [
            'event' => $event,
            'remainingSeats' => $remainingSeat,
            'previousUrl' => $request->headers->get('referer'),
        ]);
    }

}
