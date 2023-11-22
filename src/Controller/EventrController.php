<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventType;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventrController extends AbstractController
{
    protected SerializerInterface $serializer;
    public function __construct(
        SerializerInterface $serializer,
    )
    {
        $this->serializer = $serializer;
    }

    protected function makeSerializedResponse(array $response): JsonResponse
    {
        return new JsonResponse(
            $this->getSerializer()->serialize($response, 'json'),
            200,
            [],
            true
        );
    }

    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}