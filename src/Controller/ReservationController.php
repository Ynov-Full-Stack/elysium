<?php

namespace App\Controller;

use App\Entity\Reservation;
use App\Mail\MailMessage;
use App\Mail\MailService;
use App\Repository\UserRepository;
use App\Security\SupabaseUser;
use App\Security\UserResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ReservationController extends AbstractController
{

    #[Route('/reservation/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(
        Reservation $reservation,
        UserRepository $userRepository,
        MailService $mailService,
        EntityManagerInterface $entityManager,
        UserResolver $userResolver
    ): Response
    {
        /** @var SupabaseUser $securityUser */
        $securityUser = $this->getUser();
        if (!$securityUser) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_user_reservations');
        }

        $user = $userResolver->resolve($securityUser);

        if ($reservation->getUser()->getSupabaseId() !== $user->getSupabaseId()) {
            $this->addFlash('error', 'Accès non autorisé.');
            return $this->redirectToRoute('app_user_reservations');
        }

        $reservation->setStatus('annulée');
        $entityManager->persist($reservation);
        $entityManager->flush();
        $this->addFlash('success', 'Reservation annulée avec succès');
        $mailService->send(MailMessage::reservationCancellation($user, $reservation));
        $mailService->buildAndSendMessages(
            users: $userRepository->findAdmins(),
            factory: [MailMessage::class, 'adminReservationCancellation'],
            args: [$reservation]
        );

        return $this->redirectToRoute('app_user_reservations');
    }
}
