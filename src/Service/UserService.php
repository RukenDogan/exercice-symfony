<?php

namespace App\Service;

use App\Entity\User;

class UserService
{
    public function calculateAge(User $user): ?int
    {
        $birthDate = $user->getBirthDate();

        if (!$birthDate) {
            return null;
        }

        $today = new \DateTime();
        return $birthDate->diff($today)->y;
    }
}
