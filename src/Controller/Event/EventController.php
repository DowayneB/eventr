<?php

namespace App\Controller\Event;

use App\Controller\EventrController;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Guest;
use App\Entity\Location;
use App\Entity\Status;
use App\Exception\ActionProhibitedException;
use App\Exception\NotFoundException;
use App\Helper\ExceptionHelper;
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
    public function testApi(
        EventTypeManager       $eventTypeManager,
        Request                $request,
        EntityManagerInterface $entityManager,
        EventManager           $eventManager,
        LocationManager        $locationManager
    ): JsonResponse
    {
        if (!$this->get($request, 'event_type_id')) {
            return $this->makeValidationFailureResponse('event_type_id', 'Event type is required.');
        }

        $eventType = $eventTypeManager->getEventType($this->get($request, 'event_type_id'));

        if (!$eventType instanceof EventType) {
            return $this->makeValidationFailureResponse(
                "event_type_id",
                "No event type found with given ID",
                Response::HTTP_NOT_FOUND
            );
        }

        if (!$this->get($request, 'event_date')) {
            return $this->makeValidationFailureResponse("event_date","Event date is required.");
        }

        if (!$this->get($request, 'end_date')) {
            return $this->makeValidationFailureResponse("end_date","End date is required.");
        }

        if (!$this->get($request, 'rsvp_date')) {
            return $this->makeValidationFailureResponse("rsvp_date","RSVP date is required.");
        }

        $eventDate = new DateTime($this->get($request, 'event_date'));
        $endDate = new DateTime($this->get($request, 'end_date'));
        $rsvpDate = new DateTime($this->get($request, 'rsvp_date'));

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

        if (!$this->get($request, 'description')) {
            return $this->makeValidationFailureResponse(
                "description",
                "Description is required."
            );
        }

        if (!$this->get($request, 'summary')) {
            return $this->makeValidationFailureResponse(
                "summary",
                "Summary is required."
            );
        }

        if (!$this->get($request, 'location_id')) {
            return $this->makeValidationFailureResponse(
                "location_id",
                "Location is required."
            );
        }

        $location = $locationManager->findLocation($this->get($request, 'location_id'));

        if (!$location instanceof Location) {
            return $this->makeValidationFailureResponse(
                "location_id",
                "Could not find a location.",
                Response::HTTP_NOT_FOUND
            );
        }

        $event = $eventManager->createEvent(
            $eventType,
            $this->get($request, 'description'),
            $this->get($request, 'summary'),
            $location,
            $eventDate,
            $endDate,
            $rsvpDate,
            $this->getUser(),
            $this->get($request, 'private') ?? false
        );

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->makeSuccessfulResponse(
            [
                'event' => $event
            ]
        );
    }

    #[Route(
        "",
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
        "/{eventId}",
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

    /**
     * @throws NotFoundException
     * @throws ActionProhibitedException
     */
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
            throw ExceptionHelper::eventNotFoundException();
        }

        if ($event->getStatus()->getId() === Status::CANCELLED) {
            throw ExceptionHelper::alreadyActionedException();
        }

        $event->setStatus(
            $statusManager->getStatus(Status::CANCELLED)
        );

        $entityManager->flush();

        return $this->makeSuccessfulResponse([
            'event' => $event
        ]);
    }

    /**
     * @throws ActionProhibitedException
     * @throws NotFoundException
     */
    #[Route(
        "/{eventId}/make-public",
        methods: ["PUT"]
    )]
    public function makeEventPublic(
        int                    $eventId,
        EventManager           $eventManager,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            throw ExceptionHelper::eventNotFoundException();
        }

        if (!$event->isPrivate()) {
            throw ExceptionHelper::alreadyActionedException();
        }

        $event->setPrivate(false);

        $entityManager->flush();

        return $this->makeSuccessfulResponse([
            'event' => $event
        ]);
    }

    /**
     * @throws NotFoundException
     * @throws ActionProhibitedException
     */
    #[Route(
        "/{eventId}/make-private",
        methods: ["PUT"]
    )]
    public function makeEventPrivate(
        int                    $eventId,
        EventManager           $eventManager,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            throw ExceptionHelper::eventNotFoundException();
        }

        if ($event->isPrivate()) {
            throw ExceptionHelper::alreadyActionedException();
        }

        $event->setPrivate(true);

        $entityManager->flush();

        return $this->makeSuccessfulResponse([
            'event' => $event
        ]);
    }

    #[Route(
        "/{event}/invite/{guest}",
        methods: ["POST"]
    )]
    public function inviteGuest(
        Event $event,
        Guest $guest,
        EntityManagerInterface $objectManager
    ): JsonResponse
    {
        $event->addGuest($guest);
        $objectManager->persist($event);
        $objectManager->flush();

        //TODO: Send invitation mail

        return $this->makeSuccessfulResponse([
            'event' => $event
        ]);
    }

}