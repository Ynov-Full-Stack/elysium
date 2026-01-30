<?php

namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;

class SupabaseUser implements UserInterface
{
    private array $userData;

    public function __construct(array $userData)
    {
        $this->userData = $userData;
    }

    public function getUserIdentifier(): string
    {
        return $this->userData['email'];
    }

    public function getRoles(): array
    {
        return ['ROLE_USER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getSupabaseData(): array
    {
        return $this->userData;
    }

    public function getId(): ?string
    {
        return $this->userData['id'] ?? null;
    }
}
