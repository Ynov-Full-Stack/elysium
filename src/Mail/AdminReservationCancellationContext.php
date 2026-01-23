<?php

namespace App\Mail;

use App\Entity\Reservation;
use App\Entity\User;
use App\Mail\ContextInterface;

final class AdminReservationCancellationContext implements ContextInterface{
    public function __construct(
        public readonly User $user,
        public readonly Reservation $reservation,
    ) {}
}