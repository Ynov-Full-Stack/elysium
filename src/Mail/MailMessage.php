<?php

namespace App\Mail;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Reservation;
use App\Mail\Context;
use App\Mail\ReservationCreationContext;
use App\Mail\ReservationModificationContext;
use App\Mail\ReservationCancellationContext;
use App\Mail\ReservationReminderContext;
use App\Mail\AccountDeletionContext;
use App\Mail\AdminReservationCreationContext;
use App\Mail\AdminReservationCancellationContext;
use App\Mail\AdminIncidentContext;


final class MailMessage {
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $template,
        public readonly Context $context,
    ) {}
    
    public static function reservationCreation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/reservation_creation.html.twig",
            context: ContextFactory::reservationCreation($user, $reservation)
        );
    }
    public static function reservationModification(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/reservation_modification.html.twig",
            context: ContextFactory::reservationModification($user, $reservation)
        );
    }
    public static function reservationCancellation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/reservation_cancellation.html.twig",
            context: ContextFactory::reservationCancellation($user, $reservation)
        );
    }
    public static function reservationReminder(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/reservation_reminder.html.twig",
            context: ContextFactory::reservationReminder($user, $reservation)
        );
    }
    public static function accountDeletion(User $user): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/account_deletion.html.twig",
            context: ContextFactory::accountDeletion($user)
        );
    }

    public static function adminReservationCreation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/admin/reservation_creation.html.twig",
            context: ContextFactory::adminReservationCreation($user, $reservation)
        );
    }
    public static function adminReservationCancellation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/admin/reservation_cancellation.html.twig",
            context: ContextFactory::adminReservationCancellation($user, $reservation)
        );
    }
    public static function adminIncident(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Account Deletion",
            template: "emails/admin/incident_report.html.twig",
            context: ContextFactory::adminIncident($user, $reservation)
        );
    }
}

// context: ReservationMailContextBuilder::build($user,$resource,$reservation)
final class ContextFactory {

    // USER
    // Confirmation de création de réservation
    // Modification de réservation
    // Annulation de réservation
    // Rappel avant la réservation (ex : J-1 ou H-1)
    // Suppression de compte

    // ADMIN
    // Création d’une nouvelle réservation
    // Annulation d’une réservation
    // Incident critique (optionnel)

    public static function reservationCreation(User $user, Reservation $reservation): Context {
        return new ReservationCreationContext($user, $reservation);
    }
    public static function reservationModification(User $user, Reservation $reservation): Context {
        return new ReservationModificationContext($user, $reservation);
    }
    public static function reservationCancellation(User $user, Reservation $reservation): Context {
        return new ReservationCancellationContext($user, $reservation);
    }
    public static function reservationReminder(User $user, Reservation $reservation): Context {
        return new ReservationReminderContext($user, $reservation);
    }
    public static function accountDeletion(User $user): Context {
        return new AccountDeletionContext($user);
    }

    public static function adminReservationCreation(User $user, Reservation $reservation): Context {
        return new AdminReservationCreationContext($user, $reservation);
    }
    public static function adminReservationCancellation(User $user, Reservation $reservation): Context {
        return new AdminReservationCancellationContext($user, $reservation);
    }
    public static function adminIncident(User $user, Reservation $reservation): Context {
        return new AdminIncidentContext($user, $reservation);
    }

}