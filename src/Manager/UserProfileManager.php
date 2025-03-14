<?php

namespace App\Manager;

use App\Entity\UserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProfileManager
{
    public function createUserProfile(
        UserInterface $user,
        string $name,
        string $surname,
        string $cellNumber
    ): UserProfile {
        $userProfile = $user->getUserProfile();
        if (!$userProfile instanceof UserProfile) {
            $userProfile = new UserProfile();
        }
        return $userProfile->setUser($user)
            ->setName($name)
            ->setSurname($surname)
            ->setCellNumber($cellNumber);
    }
}