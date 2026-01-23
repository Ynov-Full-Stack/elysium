<?php

namespace App\Mail;

use App\Entity\User;
use App\Entity\Event;
use App\Entity\Reservation;
use App\Mail\ContextInterface;
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
        public readonly ContextInterface $context,
    ) {}
    
    // FOR ANY NEW BUILDER ALWAYS HAVE THE DESTINATION USER BE THE FIRST ARGUMENT.
    public static function reservationCreation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "New Reservation Made",
            template: "emails/reservation_creation.html.twig",
            context: ContextFactory::reservationCreation($user, $reservation)
        );
    }
    public static function reservationModification(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Reservation Edited",
            template: "emails/reservation_modification.html.twig",
            context: ContextFactory::reservationModification($user, $reservation)
        );
    }
    public static function reservationCancellation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Reservation Cancelled",
            template: "emails/reservation_cancellation.html.twig",
            context: ContextFactory::reservationCancellation($user, $reservation)
        );
    }
    public static function reservationReminder(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "Reservation Reminder",
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
            subject: "A User has Made a New Reservation",
            template: "emails/admin/reservation_creation.html.twig",
            context: ContextFactory::adminReservationCreation($user, $reservation)
        );
    }
    public static function adminReservationCancellation(User $user, Reservation $reservation): self {
        return new self(
            to: $user->getEmail(),
            subject: "A User has Cancelled their Reservation",
            template: "emails/admin/reservation_cancellation.html.twig",
            context: ContextFactory::adminReservationCancellation($user, $reservation)
        );
    }
    public static function adminIncident(
        User $user,
        String $errorType,
        String $message,
        ?String $file = null,
        ?int $line = null,
        ?String $environment = null,
        ?\DateTimeImmutable $occurredAt = null,
        ?String $requestId = null,
        ?array $stackTrace = null, // trimmed & safe
    ): self {
        return new self(
            to: $user->getEmail(),
            subject: "Important: An Incident Occurred",
            template: "emails/admin/incident_report.html.twig",
            context: ContextFactory::adminIncident($errorType, $message, $file, $line, $environment, $occurredAt ?? new \DateTimeImmutable(), $requestId, $stackTrace)
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

    public static function reservationCreation(User $user, Reservation $reservation): ContextInterface {
        return new ReservationCreationContext($user, $reservation);
    }
    public static function reservationModification(User $user, Reservation $reservation): ContextInterface {
        return new ReservationModificationContext($user, $reservation);
    }
    public static function reservationCancellation(User $user, Reservation $reservation): ContextInterface {
        return new ReservationCancellationContext($user, $reservation);
    }
    public static function reservationReminder(User $user, Reservation $reservation): ContextInterface {
        return new ReservationReminderContext($user, $reservation);
    }
    public static function accountDeletion(User $user): ContextInterface {
        return new AccountDeletionContext($user);
    }

    public static function adminReservationCreation(User $user, Reservation $reservation): ContextInterface {
        return new AdminReservationCreationContext($user, $reservation);
    }
    public static function adminReservationCancellation(User $user, Reservation $reservation): ContextInterface {
        return new AdminReservationCancellationContext($user, $reservation);
    }
    public static function adminIncident(
        string $errorType,
        string $message,
        ?string $file,
        ?int $line,
        ?string $environment,
        ?\DateTimeImmutable $occurredAt,
        ?string $requestId,
        ?array $stackTrace, // trimmed & safe
    ): ContextInterface {
        return new AdminIncidentContext($errorType, $message, $file, $line, $environment, $occurredAt, $requestId, $stackTrace);
    }

}