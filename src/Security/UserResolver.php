<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Security\SupabaseUser;
use Doctrine\ORM\EntityManagerInterface;

final class UserResolver
{
    public function __construct(
        private UserRepository         $userRepository,
        private EntityManagerInterface $em
    )
    {
    }

    public function resolve(SupabaseUser $securityUser): User
    {
        $user = $this->userRepository->findOneBy([
            'supabaseId' => $securityUser->getId(),
        ]);

        if (!$user) {
            $user = new User();
            $user->setEmail($securityUser->getUserIdentifier());
            $user->setSupabaseId($securityUser->getId());

            $displayName = $securityUser->getDisplayName()
                ?? explode('@', $securityUser->getUserIdentifier())[0];

            $user->setDisplayName($displayName);
            $user->setRoles(['ROLE_USER']);

            $this->em->persist($user);
            $this->em->flush();
        }

        return $user;
    }
}
