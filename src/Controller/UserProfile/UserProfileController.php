<?php

namespace App\Controller\UserProfile;

use App\Controller\EventrController;
use App\Manager\UserProfileManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

#[Route('/api/user-profile')]
final class UserProfileController extends EventrController
{
    #[Route(null, name: 'app_user_profile', methods: ["GET"])]
    public function index(): Response
    {
        return $this->makeSuccessfulResponse([
                'user_profile' => $this->getUser()->getUserProfile()
        ]);
    }

    #[Route(null, name: 'app_user_profile_create', methods: ["POST"])]
    public function create(Request $request,UserInterface $user, UserProfileManager $userProfileManager, EntityManagerInterface $entityManager): Response
    {
        $userProfile = $userProfileManager->createUserProfile(
            $this->getUser(),
            $request->getPayload()->get( 'name'),
            $request->getPayload()->get( 'surname'),
            $request->getPayload()->get('cell_number')
        );

        $user->setUserProfile($userProfile);

        $entityManager->persist($userProfile);
        $entityManager->flush($user);

        return $this->makeSuccessfulResponse([
            'user_profile' => $userProfile
        ]);
    }
}
