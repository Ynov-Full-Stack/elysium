<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SupabaseProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        return new SupabaseUser(['email' => $identifier]);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SupabaseUser) {
            throw new \RuntimeException('Compte non supporté');
        }
        return new SupabaseUser($user->getSupabaseData());
    }

    public function supportsClass(string $class): bool
    {
        return SupabaseUser::class === $class;
    }
}
