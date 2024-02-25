<?php

namespace App\Controller;

use App\Controller\EventType\JsonRequest;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class EventrController extends AbstractController
{
    use JsonRequest;
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