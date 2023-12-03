<?php

namespace App\Controller\Events;

use App\Controller\EventrController;
use App\Entity\Event;
use App\Entity\EventType;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/event/", name: "event_main")]
class EventController extends EventrController
{
    #[Route('create')]
    public function testApi()
    {
        $eventType = new EventType();
        $eventType->setSlug('test');
        $eventType->setDescription('test');
        $eventType->setId(1);

        $event = new Event();
        $event->setEventType($eventType);
        $event->setEventDate(new \DateTime());
        $event->setUser($this->getUser());
        $event->setRsvpDate(new \DateTime());

        return $this->makeSerializedResponse(
            [
                'event' => $event
            ]
        );
    }

    #[Route("/my-events", name: 'api_event_list_get', methods: ["GET"])]
    public function listEvents(EventManager $eventManager): JsonResponse
    {
        return $this->makeSerializedResponse(
            [
                'event' => $eventManager->getEventsByUser($this->getUser())
            ]
        );
    }

    #[Route("/{eventId}", name: 'api_event_get', methods: ["GET"])]
    public function getEvent(int $eventId, EventManager $eventManager): JsonResponse
    {
        return $this->makeSerializedResponse(
            [
                'event' => $eventManager->getEvent($eventId)
            ]
        );
    }


}