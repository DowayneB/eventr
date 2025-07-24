<?php

namespace App\Controller;

use App\Helper\JsonRequest;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

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

    protected function makeSuccessfulResponse(array $response, int $statusCode = Response::HTTP_OK): JsonResponse
    {
        return new JsonResponse(
            $this->getSerializer()->serialize($response, 'json'),
            $statusCode,
            [],
            true
        );
    }


    protected function makeValidationFailureResponse(string $field, string $message)
    {
        return $this->json(
            [
                "message" => "Validation failed",
                "errors" => [
                    $field => [
                        $message
                    ]
                ]
            ],
            Response::HTTP_BAD_REQUEST
        );
    }

    protected function getSerializer(): SerializerInterface
    {
        return $this->serializer;
    }
}