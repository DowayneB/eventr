<?php

namespace App\Controller\Guest;

use App\Controller\EventrController;
use App\Exception\ValidationException;
use App\Helper\ExceptionHelper;
use App\Helper\JsonRequest;
use App\Helper\ValidationHelper;
use App\Manager\GuestManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Doctrine\ORM\OptimisticLockException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(
    "/api/guest",
    name: "event_type_main"
)]class GuestController extends EventrController
{
    use JsonRequest;

    #[Route('/', name: 'app_guest_list', methods: ["GET"])]
    public function listGuests(GuestManager $guestManager, UserInterface $user): Response
    {
        return $this->makeSerializedResponse([
            'guests' => $guestManager->getGuestsByUser($user)
        ]);
    }

    /**
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ValidationException
     */
    #[Route('/', name: 'app_guest_create', methods: ["POST"])]
    public function createGuest(
        GuestManager $guestManager,
        UserInterface $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $name = $this->get($request,'name');
        $birthDate = $this->get($request,'birth_date');
        $cellNumber = $this->get($request,'cell_number');
        $emailAddress = $this->get($request,'email_address');

        if (!$emailAddress) {
            throw ExceptionHelper::validationFieldRequiredException('email_address');
        }

        if (!ValidationHelper::validateEmailAddress($emailAddress)) {
            throw ExceptionHelper::validationFieldIncorrectException('Incorrect value for Email address provided');
        }

        if (!$cellNumber) {
            throw ExceptionHelper::validationFieldRequiredException('cell_number');
        }

        if (!ValidationHelper::validateCellNumber($cellNumber)) {
            throw ExceptionHelper::validationFieldIncorrectException('Incorrct value for cell number provided');
        }

        if (!$name) {
            throw ExceptionHelper::validationFieldRequiredException('name');
        }

        if (!is_int($birthDate)) {
            throw ExceptionHelper::validationFieldIncorrectException('Incorrect value for birth date provided');
        }

        $guest = $guestManager->createGuest(
            $user,
            $name,
            (new \DateTime)->setTimeStamp($birthDate),
            $cellNumber,
            $emailAddress,
        );

        $entityManager->persist($guest);
        $entityManager->flush($guest);

        return $this->makeSerializedResponse([
            'guest' => $guest
        ]);
    }

}
