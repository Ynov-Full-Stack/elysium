<?php
namespace App\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class SupabaseProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // L'authentification Supabase est faite dans l'authenticator
        // On crée juste un user "vide" qui sera validé par verifySupabaseCredentials
        return new SupabaseUser(['email' => $identifier]);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof SupabaseUser) {
            throw new \RuntimeException('Compte non supporté');
        }
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return SupabaseUser::class === $class;
    }
}
