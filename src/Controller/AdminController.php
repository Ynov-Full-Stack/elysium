<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController {

    #[Route('/events', name: 'event_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findAll();
        return $this->render('pages/admin/index.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route(path: '/event/new', name: 'event_add', methods: ['GET', 'POST'])]
    public function add_event(Request $request, EntityManagerInterface $entityManager): Response {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();

            return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('pages/admin/create.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route(path: '/event/{id}', name: 'event_delete', methods: ["POST"])]
    public function remove_event(Event $event, Request $request, EntityManagerInterface $entityManager): Response {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }
        return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);

    }

    #[Route('/event/{id}', name: 'event_edit', methods: ['GET', 'POST'])]
    public function edit(Event $event, Request $request, EntityManagerInterface $entityManager
    ): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($event);
            $entityManager->flush();
            return $this->redirectToRoute('event_index');
        }
        return $this->render('pages/admin/edit.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

}
