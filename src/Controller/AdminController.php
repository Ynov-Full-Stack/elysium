<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[Route('/admin')]
#[IsGranted('ROLE_ADMIN')]
final class AdminController extends AbstractController {
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager) {
        $this->entityManager = $entityManager;
    }

    #[Route(path: '/event/new', name: 'event_add', methods: ['GET', 'POST'])]
    public function add_event(Request $request): Response {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($event);
            $this->entityManager->flush();

            return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/create.html.twig', [
            'form' => $form->createView(),
            'controller_name' => 'AdminController',
        ]);
    }
    
    #[Route(path: '/event/{id}', name: 'event_delete', methods: ["DELETE"])]
    public function remove_event(Request $request, Event $event): Response {
        if ($this->isCsrfTokenValid('delete' . $event->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }
        return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);

    }
    
    #[Route(path: '/event/{id}', name: 'event_edit', methods: ["PUT"])]
    public function index(Request $request ,Event $event): Response {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return $this->redirectToRoute('event_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->render('admin/index.html.twig', [
			'form' => $form->createView(),
            'controller_name' => 'AdminController',
        ]);
    }
}