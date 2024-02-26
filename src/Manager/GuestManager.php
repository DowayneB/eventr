<?php

namespace App\Manager;

use App\Entity\Guest;
use App\Repository\GuestRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\User\UserInterface;

class GuestManager
{
    private GuestRepository $repository;

    public function __construct(GuestRepository $guestRepository)
    {
        $this->repository = $guestRepository;
    }

    public function getGuestsByUser(UserInterface $user)
    {
        return $this->getRepository()->findBy([
            'user' => $user
        ]);
    }

    private function getRepository(): GuestRepository
    {
        return $this->repository;
    }

    public function createGuest(
        UserInterface $user,
        string $name,
        \DateTimeInterface $birthDate,
        string $cellNumber,
        string $emailAddress
    ): Guest
    {
        $guest = new Guest();
        $guest->setUser($user);
        $guest->setName($name);
        $guest->setBirthDate($birthDate);
        $guest->setCellNumber($cellNumber);
        $guest->setEmailAddress($emailAddress);

        return $guest;
    }
}