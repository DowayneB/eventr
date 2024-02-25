<?php

namespace App\Controller\Event;

use App\Controller\EventrController;
use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Status;
use App\Exception\ActionProhibitedException;
use App\Exception\NotFoundException;
use App\Helper\ExceptionHelper;
use App\Manager\EventManager;
use App\Manager\EventTypeManager;
use App\Manager\StatusManager;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    "/api/event",
    name: "event_main"
)]
class EventController extends EventrController
{
    #[Route(
        '/types',
        name: 'api_event_types_list',
        methods: ["GET"]
    )]
    public function listEventTypes(
        EventTypeManager $eventTypeManager
    ): JsonResponse
    {
        return $this->makeSerializedResponse(
            [
                'event_types' => $eventTypeManager->getEventTypes()
            ]
        );
    }

    /**
     * @throws Exception
     */
    #[Route(
        null,
        name: "api_event_create_post",
        methods: ["POST"]
    )]
    public function testApi(
        EventTypeManager $eventTypeManager,
        Request $request,
        EntityManagerInterface $entityManager,
        EventManager $eventManager
    ): JsonResponse
    {
        if (!$request->get('event_type_id')) {
            throw ExceptionHelper::validationFieldRequiredException("event_type_id");
        }

        $eventType = $eventTypeManager->getEventType($request->get('event_type_id'));

        if (!$eventType instanceof EventType) {
            throw new Exception("No event type found with given ID");
        }

        if (!$request->get('event_date')) {
            throw ExceptionHelper::validationFieldRequiredException("event_date");
        }

        if (!$request->get('rsvp_date')) {
            throw ExceptionHelper::validationFieldRequiredException("rsvp_date");
        }

        $eventDate = new DateTime($request->get('event_date'));
        $rsvpDate = new DateTime($request->get('rsvp_date'));

        if (DateTimeImmutable::createFromMutable($eventDate)->modify('- 3 day') < new DateTime())
        {
            throw ExceptionHelper::validationFieldIncorrectException(
                "Events must be created at least 3 days before the event takes place"
            );
        }

        if ($rsvpDate >= (new DateTime())->setTimestamp($eventDate->getTimestamp())->modify('-1 day'))
        {
            throw ExceptionHelper::validationFieldIncorrectException(
                "RSVP date must be at least 1 day before the event takes place"
            );
        }

        if ($rsvpDate <= new \DateTime('now'))
        {
            throw ExceptionHelper::validationFieldIncorrectException(
                "RSVP date cannot be in the past"
            );
        }

        if (!$request->get('description')) {
            throw ExceptionHelper::validationFieldRequiredException(
                'Description'
            );
        }

        $event = $eventManager->createEvent(
            $eventType,
            $request->get('description'),
            $eventDate,
            $rsvpDate,
            $this->getUser()
        );

        $entityManager->persist($event);
        $entityManager->flush();

        return $this->makeSerializedResponse(
            [
                'event' => $event
            ]
        );
    }

    #[Route(
        "/",
        name: 'api_event_list_get',
        methods: ["GET"]
    )]
    public function listEvents(
        EventManager $eventManager
    ): JsonResponse
    {
        return $this->makeSerializedResponse(
            [
                'event' => $eventManager->getEventsByUser($this->getUser())
            ]
        );
    }

    #[Route(
        "/{eventId}",
        name: 'api_event_get',
        methods: ["GET"]
    )]
    public function getEvent(
        int $eventId,
        EventManager $eventManager
    ): JsonResponse
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
        int $eventId,
        EventManager $eventManager,
        StatusManager $statusManager,
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

        return $this->makeSerializedResponse([
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
        int $eventId,
        EventManager $eventManager,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event)
        {
            throw ExceptionHelper::eventNotFoundException();
        }

        if (!$event->isPrivate())
        {
            throw ExceptionHelper::alreadyActionedException();
        }

        $event->setPrivate(false);

        $entityManager->flush();

        return $this->makeSerializedResponse([
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
        int $eventId,
        EventManager $eventManager,
        EntityManagerInterface $entityManager
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event)
        {
            throw ExceptionHelper::eventNotFoundException();
        }

        if ($event->isPrivate())
        {
            throw ExceptionHelper::alreadyActionedException();
        }

        $event->setPrivate(true);

        $entityManager->flush();

        return $this->makeSerializedResponse([
            'event' => $event
        ]);
    }

}