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
}