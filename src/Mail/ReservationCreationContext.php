<?php

namespace App\Mail;

use App\Entity\Reservation;
use App\Entity\User;

final class ReservationCreationContext implements ContextInterface{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}
