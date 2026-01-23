<?php

namespace App\Mail;

use App\Entity\Reservation;
use App\Entity\User;
use App\Mail\ContextInterface;

final class AccountDeletionContext implements ContextInterface{
    public function __construct(
        public readonly User $user
    ) {}
}