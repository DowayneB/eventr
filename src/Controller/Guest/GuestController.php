<?php

namespace App\Controller\Guest;

use App\Controller\EventrController;
use App\Entity\Guest;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route(
    "/api/guest",
    name: "guest_main"
)]
class GuestController extends EventrController
{
    use JsonRequest;

    #[Route('', name: 'api_guests', methods: ["GET"])]
    public function listGuests(GuestManager $guestManager, UserInterface $user): Response
    {
        return $this->makeSuccessfulResponse([
            'guests' => $guestManager->getGuestsByUser($user)
        ]);
    }

    #[Route(
        '/{guest_id}',
        name: 'api_get_guest',
        methods: ["GET"]
    )]
    public function getGuest(
        GuestManager  $guestManager,
        UserInterface $user,
        Request       $request
    ): Response
    {
        $guest = $guestManager->findGuest($request->get('guest_id'), $user);

        if (!$guest instanceof Guest) {
            return $this->makeValidationFailureResponse(
                'guest_id',
                "Guest with ID {$request->get('guest_id')} not found.",
                Response::HTTP_NOT_FOUND
            );
        }

        return $this->makeSuccessfulResponse([
            'guest' => $guest
        ]);
    }


    #[Route('', name: 'api_guest_create', methods: ["POST"])]
    public function createGuest(
        GuestManager $guestManager,
        UserInterface $user,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response
    {
        $name = $this->get($request,'name');
        $age = $this->get($request,'age');
        $cellNumber = $this->get($request,'cell_number');
        $emailAddress = $this->get($request,'email_address');

        if (!$emailAddress) {
            return $this->makeValidationFailureResponse('email_address', "Email address is required");
        }

        if (!ValidationHelper::validateEmailAddress($emailAddress)) {
            return $this->makeValidationFailureResponse('email_address', "Email address is not valid");
        }

        if (!$cellNumber) {
            return $this->makeValidationFailureResponse('cell_number', "Cell number is required");
        }

        if (!ValidationHelper::validateCellNumber($cellNumber)) {
            return $this->makeValidationFailureResponse('cell_number', "Cell number is not valid");
        }

        if (!$name) {
            return $this->makeValidationFailureResponse('name', "Name is required");
        }

        if (!is_int($age)) {
            return $this->makeValidationFailureResponse('age', "Age is required");
        }

        $guest = $guestManager->createGuest(
            $user,
            $name,
            $age,
            $cellNumber,
            $emailAddress,
        );

        $entityManager->persist($guest);
        $entityManager->flush();

        return $this->makeSuccessfulResponse(
            ['guest' => $guest],
            Response::HTTP_CREATED,
            [
                "Location" => $this->generateUrl('guest_mainapi_get_guest', ['guest_id' => $guest->getId()], UrlGeneratorInterface::ABSOLUTE_URL)
            ]
        );
    }

}
