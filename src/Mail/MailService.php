<?php

namespace App\Mail;

use Symfony\Component\Messenger\MessageBusInterface;

final class MailService
{
    public function __construct(
        private MessageBusInterface $bus
    ) {}

    public function send(MailMessage $message): void
    {
        $this->bus->dispatch($message);
    }
}
