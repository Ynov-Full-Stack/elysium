<?php

namespace App\Mail;

use App\Mail\Context;

final class AdminIncidentContext implements Context{
    public function __construct(
        public readonly string $errorType,
        public readonly string $message,
        public readonly ?string $file,
        public readonly ?int $line,
        public readonly ?string $environment,
        public readonly \DateTimeImmutable $occurredAt,
        public readonly ?string $requestId,
        public readonly ?array $stackTrace, // trimmed & safe
    ) {}
}