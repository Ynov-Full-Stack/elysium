<?php

namespace App\Mail;

final class MailMessage {
    public function __construct(
        public readonly string $to,
        public readonly string $subject,
        public readonly string $template,
        public readonly array $context = [],
    ) {}
}
