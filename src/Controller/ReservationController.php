<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Mail\MailMessage;
use App\Mail\MailService;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{
    public function __construct(private readonly MailService $mailService, private readonly UserRepository $userRepository) {}

    #[Route('/reservation/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Reservation $reservation, EntityManagerInterface $entityManager): Response
    {
        if ($reservation->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }
        $reservation->setStatus('annulée');
        $entityManager->persist($reservation);
        $entityManager->flush();

        $this->mailService->send(MailMessage::reservationCancellation($this->getUser(), $reservation));
        $this->mailService->buildAndSendMessages(
            users: $this->userRepository->findAdmins(),
            factory: [MailMessage::class, 'adminReservationCancellation'],
            args: [$reservation]
        );

        $this->addFlash('success', 'Reservation annulée avec succès');

        return $this->redirectToRoute('app_user_reservations');
    }
}
