<?php

namespace App\Manager;

use App\Entity\UserProfile;
use App\Repository\UserProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserProfileManager
{
    private UserProfileRepository $repository;

    public function __construct(
        UserProfileRepository $userProfileRepository
    )
    {
        $this->repository = $userProfileRepository;
    }

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

    public function getUserProfileByUserId(int $userId): ?UserProfile
    {
        return $this->getUserProfileRepository()->find($userId);
    }

    private function getUserProfileRepository(): UserProfileRepository
    {
        return $this->repository;
    }
}