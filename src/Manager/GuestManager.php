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
        int $age,
        string $cellNumber,
        string $emailAddress
    ): Guest
    {
        $guest = new Guest();
        $guest->setUser($user);
        $guest->setName($name);
        $guest->setAge($age);
        $guest->setCellNumber($cellNumber);
        $guest->setEmailAddress($emailAddress);

        return $guest;
    }

    public function findGuest(
        int $id,
        UserInterface $user
    ): ?Guest
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
            'user' => $user
        ]);
    }
}