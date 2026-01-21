<?php


namespace App\Mail;

use App\Entity\User;
use App\Entity\Reservation;

interface Context{}

final class ReservationCreationContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
final class ReservationModificationContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
final class ReservationCancellationContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
final class ReservationReminderContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
final class AccountDeletionContext implements Context{
    public function __construct(
        public readonly User $user
    ) {}
}
final class AdminReservationCreationContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
final class AdminReservationCancellationContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
final class AdminIncidentContext implements Context{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}