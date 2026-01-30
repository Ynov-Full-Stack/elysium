<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\User;
use App\Mail\MailMessage;
use App\Mail\MailService;
use App\Repository\UserRepository;
use App\Security\UserResolver;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly UserRepository $userRepository,

    ) {}

    #[Route('/checkout/{id}', name: 'stripe_checkout', methods: ['POST'])]
    public function checkout(Event $event, Request $request, UrlGeneratorInterface $urlGenerator, UserResolver $userResolver): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');
        $quantity = max(1, (int) $request->request->get('quantity', 1));

        if ($quantity > $event->getRemainingSeats()) {
            $this->addFlash('error', 'Pas assez de places disponibles');
            return $this->redirectToRoute('app_event_show', [
                'id' => $event->getId()
            ]);
        }

        Stripe::setApiKey($this->getParameter('stripe.secret_key'));

        $successUrl = $urlGenerator->generate('stripe_success', [],
                UrlGeneratorInterface::ABSOLUTE_URL
            ) . '?session_id={CHECKOUT_SESSION_ID}';

        $cancelUrl = $urlGenerator->generate('stripe_cancel', [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );
        $user = $userResolver->resolve($this->getUser());

        /** @var Création de la session avec les références des produits $session */
        $session = Session::create([
            'mode' => 'payment',
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ($event->getPrice() * 100),
                    'product_data' => [
                        'name' => $event->getName(),
                    ],
                ],
                'quantity' => $quantity,
            ]],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'metadata' => [
                'event_id' => $event->getId(),
                'user_id' => $user->getId(),
                'quantity' => $quantity,
            ],
        ]);

        return $this->redirect($session->url);
    }

    #[Route('/success', name: 'stripe_success')]
    public function success(
        Request $request,
        EntityManagerInterface $entityManager,

    ): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Session Stripe manquante.');
            return $this->redirectToRoute('app_event_index');
        }

        Stripe::setApiKey($this->getParameter('stripe.secret_key'));

        $session = Session::retrieve($sessionId);

        if ($session->payment_status !== 'paid') {
            $this->addFlash('error', 'Le paiement n’a pas été confirmé.');
            return $this->redirectToRoute('app_event_index');
        }

        // Vérifie si la réservation existe déjà
        $existingReservation = $entityManager->getRepository(Reservation::class)
            ->findOneBy(['stripeSessionId' => $session->id]);

        if (!$existingReservation) {
            $eventId = $session->metadata->event_id;
            $userId = $session->metadata->user_id;
            $quantity = $session->metadata->quantity;

            $event = $entityManager->getRepository(Event::class)->find($eventId);
            $user = $entityManager->getRepository(User::class)->find($userId);

            if ($event && $user) {
                $reservation = new Reservation();
                $reservation->setEvent($event);
                $reservation->setUser($user);
                $reservation->setSeatQuantity($quantity);
                $reservation->setStatus('en cours');
                $reservation->setStripeSessionId($session->id);

                $this->mailService->send(MailMessage::reservationCreation($user, $reservation));
                $this->mailService->buildAndSendMessages(
                    users: $this->userRepository->findAdmins(),
                    factory: [MailMessage::class, 'adminReservationCreation'],
                    args: [$reservation]
                );

                $entityManager->persist($reservation);
                $entityManager->flush();
            }
        }

        $this->addFlash('success', 'Paiement confirmé. Votre réservation est visible dans votre profil.');
        return $this->redirectToRoute('app_user_reservations');
    }

    #[Route('/cancel', name: 'stripe_cancel')]
    public function cancel(): Response
    {
        $this->addFlash('error', 'Paiement annulé');
        return $this->redirectToRoute('app_event_index');
    }
}
