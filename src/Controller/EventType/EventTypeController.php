<?php

namespace App\Controller\EventType;

use App\Controller\EventrController;
use App\Entity\EventType;
use App\Manager\EventTypeManager;
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    "/api/event-type",
    name: "event_type_main"
)]
class EventTypeController extends AbstractController
{
    #[Route('', name: 'api_event_types')]
    public function index(
        EventTypeManager $eventTypeManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize(['event-types' => $eventTypeManager->getEventTypes()], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }

    #[Route('/{eventTypeId}', name: 'api_event_type')]
    public function eventType(
        int              $eventTypeId,
        EventTypeManager $eventTypeManager,
        SerializerInterface $serializer
    ): Response {

        $eventType = $eventTypeManager->getEventType($eventTypeId);

        if (!$eventType instanceof EventType) {
            return new JsonResponse(
                $serializer->serialize(['errors' => [
                    'field' => 'eventTypeId',
                    'message' => "Event type with id {$eventTypeId} not found",
                ]], 'json'),
                Response::HTTP_NOT_FOUND,
                [],true
            );
        }

        return new JsonResponse(
            $serializer->serialize(['event-type' => $eventType], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }
}
