<?php

namespace App\Controller\Event;

use App\Controller\EventrController;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Location;
use App\Entity\Status;
use App\Manager\EventManager;
use App\Manager\EventTypeManager;
use App\Manager\LocationManager;
use App\Manager\StatusManager;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    "/api/event",
    name: "event_main"
)]
class EventController extends EventrController
{
    #[Route(
        null,
        name: "api_event_create_post",
        methods: ["POST"]
    )]
    public function createEvent(
        EventTypeManager       $eventTypeManager,
        Request                $request,
        EntityManagerInterface $entityManager,
        EventManager           $eventManager,
        LocationManager        $locationManager
    ): JsonResponse
    {
        if (!$request->getPayload()->get( 'event_type_id')) {
            return $this->makeValidationFailureResponse('event_type_id', 'Event type is required.');
        }

        $eventType = $eventTypeManager->getEventType($request->getPayload()->get( 'event_type_id'));

        if (!$eventType instanceof EventType) {
            return $this->makeValidationFailureResponse(
                "event_type_id",
                "No event type found with given ID",
                Response::HTTP_NOT_FOUND
            );
        }

        if (!$request->getPayload()->get( 'event_date')) {
            return $this->makeValidationFailureResponse("event_date","Event date is required.");
        }

        if (!$request->getPayload()->get( 'end_date')) {
            return $this->makeValidationFailureResponse("end_date","End date is required.");
        }

        if (!$request->getPayload()->get( 'rsvp_date')) {
            return $this->makeValidationFailureResponse("rsvp_date","RSVP date is required.");
        }

        $eventDate = new DateTime($request->getPayload()->get( 'event_date'));
        $endDate = new DateTime($request->getPayload()->get( 'end_date'));
        $rsvpDate = new DateTime($request->getPayload()->get('rsvp_date'));

        if (DateTimeImmutable::createFromMutable($eventDate)->modify('- 3 day') < new DateTime()) {
            return $this->makeValidationFailureResponse(
                "event_date",
                "Events must be created at least 3 days before the event takes place."
            );
        }

        if ($endDate <= $eventDate) {
            return $this->makeValidationFailureResponse(
                "end_date",
                "End date must be greater than start date."
            );
        }

        if ($rsvpDate >= (new DateTime())->setTimestamp($eventDate->getTimestamp())->modify('-1 day')) {
            return $this->makeValidationFailureResponse(
                "rsvp_date",
                "RSVP must be at least 1 day before the event takes place."
            );
        }

        if ($rsvpDate <= new \DateTime('now')) {
            return $this->makeValidationFailureResponse(
                "rsvp_date",
                "RSVP date can not be in the past"
            );
        }

        if (!$request->getPayload()->get( 'description')) {
            return $this->makeValidationFailureResponse(
                "description",
                "Description is required."
            );
        }

        if (!$request->getPayload()->get( 'summary')) {
            return $this->makeValidationFailureResponse(
                "summary",
                "Summary is required."
            );
        }

        if (!$request->getPayload()->get( 'location_id')) {
            return $this->makeValidationFailureResponse(
                "location_id",
                "Location is required."
            );
        }

        $location = $locationManager->findLocation($request->getPayload()->get('location_id'));

        if (!$location instanceof Location) {
            return $this->makeValidationFailureResponse(
                "location_id",
                "Could not find a location.",
                Response::HTTP_NOT_FOUND
            );
        }

        $event = $eventManager->createEvent(
            $eventType,
            $request->getPayload()->get( 'description'),
            $request->getPayload()->get( 'summary'),
            $location,
            $eventDate,
            $endDate,
            $rsvpDate,
            $this->getUser(),
            $request->getPayload()->get( 'private') ?? false
        );

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->makeSuccessfulResponse(
            [
                'event' => $event
            ],
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'event_mainapi_event_get',
                    [
                        'event_id' => $event->getId()
                    ]
                )
            ]
        );
    }

    #[Route(
        null,
        name: 'api_event_list_get',
        methods: ["GET"]
    )]
    public function listEvents(
        EventManager $eventManager
    ): JsonResponse
    {
        return $this->makeSuccessfulResponse(
            [
                'event' => $eventManager->getActiveEventsByUser($this->getUser())
            ]
        );
    }

    #[Route(
        "/public",
        name: 'api_public_event_list_get',
        methods: ["GET"]
    )]
    public function listPublicEvents(
        EventManager $eventManager
    ): JsonResponse
    {
        return $this->makeSuccessfulResponse(
            [
                'event' => $eventManager->getPublicEventsForUser($this->getUser())
            ]
        );
    }

    #[Route(
        "/{event_id}",
        name: 'api_event_get',
        methods: ["GET"]
    )]
    public function getEvent(
        int          $eventId,
        EventManager $eventManager
    ): JsonResponse
    {
        return $this->makeSuccessfulResponse(
            [
                'event' => $eventManager->getEvent(
                    $eventId,
                    $this->getUser()
                )
            ]
        );
    }

    #[Route(
        "/{eventId}/cancel",
        name: 'api_event_put',
        methods: ["DELETE"]
    )]
    public function cancelEvent(
        int                    $eventId,
        EventManager           $eventManager,
        StatusManager          $statusManager,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            return $this->makeValidationFailureResponse('event_id', "Event with ID {$eventId} not found.");
        }

        if ($event->getStatus()->getId() === Status::CANCELLED) {
            return $this->makeSuccessfulResponse([
                'event' => $event
            ]);
        }

        $event->setStatus(
            $statusManager->getStatus(Status::CANCELLED)
        );

        $entityManager->flush();

        return $this->makeSuccessfulResponse([
            'event' => $event
        ]);
    }

    #[Route(
        "/{eventId}/visibility",
        methods: ["PATCH"]
    )]
    public function makeEventPublic(
        int                    $eventId,
        EventManager           $eventManager,
        EntityManagerInterface $entityManager,
        Request                $request
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            return $this->makeValidationFailureResponse('event_id', "Event with ID {$eventId} not found.");
        }

        if ($request->getPayload()->get('is_private') === null) {
            return $this->makeValidationFailureResponse('is_private', "is_private is required.");
        }

        if ($event->isPrivate() === $request->getPayload()->get( 'is_private')) {
            return $this->makeSuccessfulResponse([
                'event' => $event
            ]);
        }

        $event->setPrivate((bool)$request->getPayload()->get('is_private'));

        $entityManager->flush();

        return $this->makeSuccessfulResponse([
            'event' => $event
        ]);
    }
}