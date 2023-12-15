<?php

namespace App\Controller\Events;

use App\Controller\EventrController;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Status;
use App\Manager\EventManager;
use App\Manager\EventTypeManager;
use App\Manager\StatusManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/api/event", name: "event_main")]
class EventController extends EventrController
{
    #[Route('/types', name: 'api_event_types_list', methods: ["GET"])]
    public function listEventTypes(EventTypeManager $eventTypeManager): JsonResponse
    {
        return $this->makeSerializedResponse(
            [
                'event_types' => $eventTypeManager->getEventTypes()
            ]
        );
    }

    #[Route('', name: "api_event_create_post", methods: ["POST"])]
    public function testApi(
        EventTypeManager $eventTypeManager,
        Request $request,
        EntityManagerInterface $entityManager,
        StatusManager $statusManager
    ): JsonResponse
    {
        if (!$request->get('event_type_id')) {
            throw new \Exception('Event type ID must be supplied');
        }

        $eventType = $eventTypeManager->getEventType($request->get('event_type_id'));

        if (!$eventType instanceof EventType) {
            throw new \Exception("No event type found with given ID");
        }

        if (!$request->get('event_date')) {
            throw new \Exception("Event date must be supplied");
        }

        if (!$request->get('rsvp_date')) {
            throw new \Exception("RSVP date must be supplied");
        }

        $event = new Event();
        $event->setEventType($eventType);
        $event->setEventDate(new \DateTime($request->get('event_date')));
        $event->setUser($this->getUser());
        $event->setRsvpDate(new \DateTime($request->get('rsvp_date')));
        $event->setStatus(
            $statusManager->getStatus(Status::PRIVATE)
        );

        $entityManager->persist($event);
        $entityManager->flush();

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
                'event' => $eventManager->getEvent(
                    $eventId,
                    $this->getUser()
                )
            ]
        );
    }

    #[Route("/{eventId}/cancel", name: 'api_event_put', methods: ["DELETE"])]
    public function cancelEvent(
        int $eventId,
        EventManager $eventManager,
        StatusManager $statusManager,
        EntityManagerInterface $entityManager
    )
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            throw new \Exception('Event not found');
        }

        if ($event->getStatus()->getId() === Status::CANCELLED) {
            throw new \Exception('Event already cancelled');
        }

        $event->setStatus(
            $statusManager->getStatus(Status::CANCELLED)
        );

        $entityManager->flush();

        return $this->makeSerializedResponse([
            'event' => $event
        ]);
    }


}