<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;

class UserResolver
{
    public function resolve(SupabaseUser $supabaseUser): SupabaseUser
    {
        return $supabaseUser;
    }
}
