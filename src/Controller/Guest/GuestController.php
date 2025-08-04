<?php

namespace App\Controller\Guest;

use App\Entity\Guest;
use App\Manager\GuestManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route(
    "/api/guest",
)]
class GuestController extends AbstractController
{
    #[Route('', name: 'api_guests', methods: ["GET"])]
    public function listGuests(
        GuestManager $guestManager,
        UserInterface $user,
        SerializerInterface $serializer,
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize(['guests' => $guestManager->getGuestsByUser($user)], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }

    #[Route(
        '/{guestId}',
        name: 'api_get_guest',
        methods: ["GET"]
    )]
    public function getGuest(
        int $guestId,
        GuestManager  $guestManager,
        UserInterface $user,
        SerializerInterface $serializer,
    ): Response
    {
        $guest = $guestManager->findGuest($guestId, $user);

        if (!$guest instanceof Guest) {
            return new JsonResponse(
                $serializer->serialize(['errors' => [
                    'field' => 'guestId',
                    'message' => "Guest with id {$guestId} not found",
                ]], 'json'),
                Response::HTTP_NOT_FOUND,
                [],true
            );
        }

        return new JsonResponse(
            $serializer->serialize(['guest' => $guest], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }


    #[Route('', name: 'api_guest_create', methods: ["POST"])]
    public function createGuest(
        GuestManager $guestManager,
        UserInterface $user,
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response
    {
        $constraints = new Assert\Collection([
            'name' => new Assert\Type('string'),
            'age' => new Assert\Type('integer'),
            'cell_number' => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
                new Assert\Regex([
                    'pattern' => '/^(\+?\d{1,3}[- ]?)?\d{10}$/',
                    'message' => 'Please enter a valid cell number.',
                ]),
            ],
            'email_address' => [
                new Assert\NotBlank(),
                new Assert\Email([
                    'message' => 'Please enter a valid email address.',
                ]),
            ]
        ]);

        $violations = $validator->validate($request->getPayload()->all(),$constraints);

        if (count($violations) > 0) {
            $errors = array_map(function ($violation) {
                return [
                    'field' => trim($violation->getPropertyPath(), '[]'),
                    'message' => $violation->getMessage(),
                ];
            },[...$violations]);

            return new JsonResponse(
                $serializer->serialize(['errors' => $errors], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        $name = $request->getPayload()->get('name');
        $age = $request->getPayload()->get('age');
        $cellNumber = $request->getPayload()->get('cell_number');
        $emailAddress = $request->getPayload()->get('email_address');

        $guest = $guestManager->createGuest(
            $user,
            $name,
            $age,
            $cellNumber,
            $emailAddress,
        );

        $entityManager->persist($guest);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize(['guest' => $guest], 'json'),
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl('api_get_guest', ['guestId' => $guest->getId()]),
            ],true
        );
    }

}
