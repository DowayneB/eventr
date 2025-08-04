<?php

namespace App\Controller\UserProfile;

use App\Manager\UserProfileManager;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[Route('/api/user-profile')]
final class UserProfileController extends AbstractController
{
    #[Route(null, name: 'app_user_profile', methods: ["GET"])]
    public function index(
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize(['user_profile' => $this->getUser()->getUserProfile()], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }

    #[Route(null, name: 'app_user_profile_create', methods: ["POST"])]
    public function create(
        Request $request,
        UserInterface $user,
        UserProfileManager $userProfileManager,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        ValidatorInterface $validator
    ): Response {
        $constraints = new Assert\Collection([
            'name' => new Assert\Type('string'),
            'surname' => new Assert\Type('string'),
            'cell_number' => [
                new Assert\Type('string'),
                new Assert\NotBlank(),
                new Assert\Regex([
                    'pattern' => '/^(\+?\d{1,3}[- ]?)?\d{10}$/',
                    'message' => 'Please enter a valid cell number.',
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

        $userProfile = $userProfileManager->createUserProfile(
            $this->getUser(),
            $request->getPayload()->get( 'name'),
            $request->getPayload()->get( 'surname'),
            $request->getPayload()->get('cell_number')
        );

        $user->setUserProfile($userProfile);

        $entityManager->persist($userProfile);
        $entityManager->flush($user);

        return new JsonResponse(
            $serializer->serialize(['user_profile' => $userProfile], 'json'),
            Response::HTTP_CREATED,true
        );
    }
}
