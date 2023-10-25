<?php

namespace App\Controller;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use const JSON_PRETTY_PRINT;

class IndexController extends AbstractController
{
    private SerializerInterface $serializer;
    public function __construct(
        SerializerInterface $serializer
    )
    {
        $this->serializer = $serializer;
    }

    #[Route('/index', name: 'app_index')]
    public function index(): Response
    {
        return $this->render('index/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }

    #[Route('/index/est', name: 'app_indewx')]
    public function indewx(UserInterface $user): Response
    {
        $context = SerializationContext::create()->setGroups(
            'password'
        );

        return new JsonResponse(
            $this->getSerializer()->serialize(
                $user, 'json',
                $context
            ),
            200,
            [],
            JSON_PRETTY_PRINT
        );
    }

    private function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}
