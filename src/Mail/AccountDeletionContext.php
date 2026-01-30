<?php

namespace App\Mail;


use App\Entity\User;

final class AccountDeletionContext implements ContextInterface{
    public function __construct(
        public readonly User $user
    ) {}
}
