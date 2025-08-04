<?php

namespace App\Controller\Event;

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
use JMS\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    "/api/event",
)]
class EventController extends AbstractController
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
        LocationManager        $locationManager,
        ValidatorInterface     $validator,
        SerializerInterface    $serializer
    ): JsonResponse
    {
        $constraints = new Assert\Collection([
            'event_type_id' => new Assert\Type('integer'),
            'event_date' => new Assert\Type('integer'),
            'end_date' => new Assert\Type('integer'),
            'rsvp_date' => new Assert\Type('integer'),
            'description' => new Assert\Type('string'),
            'summary' => new Assert\Type('string'),
            'location_id' => new Assert\Type('integer'),
            'private' => new Assert\Optional([
                new Assert\Type('boolean'),
            ])
        ]);

        $violations = $validator->validate(
            $request->getPayload()->all()
            ,$constraints
        );

        if (count($violations) > 0) {
            $errors = array_map(function ($violation) {
                return [
                    'field' => trim($violation->getPropertyPath(), '[]'),
                    'message' => $violation->getMessage(),
                ];
            },[...$violations]);

            return new JsonResponse(
                $serializer->serialize(['errors' => $errors], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        $eventType = $eventTypeManager->getEventType($request->getPayload()->get( 'event_type_id'));

        if (!$eventType instanceof EventType) {
            return new JsonResponse(
                $serializer->serialize(['errors' => [
                    [
                        'field' => 'event_type_id',
                        "message" => "Event type not found for ID {$request->getPayload()->get( 'event_type_id' )}."
                    ]
                ]], 'json'),
                Response::HTTP_NOT_FOUND,
                [],true
            );
        }

        $eventDate = (new DateTime())->setTimestamp($request->getPayload()->get( 'event_date'));
        $endDate = (new DateTime())->setTimestamp($request->getPayload()->get( 'end_date'));
        $rsvpDate = (new DateTime())->setTimestamp($request->getPayload()->get('rsvp_date'));

        if (DateTimeImmutable::createFromMutable($eventDate)->modify('- 3 day') < new DateTime()) {
            return new JsonResponse(
                $serializer->serialize(['errors' => [
                    [
                        'field' => 'event_date',
                        "message" => "Event date must be less than 3 days before start date."
                    ]
                ]], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        if ($endDate <= $eventDate) {
            return new JsonResponse(
                $serializer->serialize(["errors" => [
                    [
                        "field" => "end_date",
                        "message" => "End date must be greater than end date."
                    ]
                ]
                ], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        if ($rsvpDate >= (new DateTime())->setTimestamp($eventDate->getTimestamp())->modify('-1 day')) {
            return new JsonResponse(
                $serializer->serialize(["errors" => [
                    [
                        "field" => "rsvp_date",
                        "message" => "RSVP date must be less than start date."
                    ]
                ]], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        if ($rsvpDate <= new \DateTime('now')) {
            return new JsonResponse(
                $serializer->serialize(["errors" => [
                    [
                        "field" => "rsvp_date",
                        "message" => "RSVP date must be in the future."
                    ]
                ]], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        $location = $locationManager->findLocation($request->getPayload()->get('location_id'));

        if (!$location instanceof Location) {
            return new JsonResponse(
                $serializer->serialize(['errors' => [
                    [
                        "field" => "location_id",
                        "message" => "Location not found for ID {$request->getPayload()->get( 'location_id' )}."
                    ]
                ]], 'json'),
                Response::HTTP_NOT_FOUND,
                [],true
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
            $request->getPayload()->get( 'private', false)
        );

        $entityManager->persist($event);
        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize([
                'event' => $event
            ],'json'),
            Response::HTTP_CREATED,
            [
                'Location' => $this->generateUrl(
                    'api_event_get',
                    [
                        'eventId' => $event->getId()
                    ]
                )
            ],true
        );
    }

    #[Route(
        null,
        name: 'api_event_list_get',
        methods: ["GET"]
    )]
    public function listEvents(
        EventManager $eventManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize([
                'events' => $eventManager->getActiveEventsByUser($this->getUser())
            ], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }

    #[Route(
        "/public",
        name: 'api_public_event_list_get',
        methods: ["GET"]
    )]
    public function listPublicEvents(
        EventManager $eventManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize([
                'events' => $eventManager->getPublicEventsForUser($this->getUser())
            ], 'json'),
            Response::HTTP_OK,
            [],true
        );
    }

    #[Route(
        "/{eventId}",
        name: 'api_event_get',
        methods: ["GET"]
    )]
    public function getEvent(
        int                 $eventId,
        EventManager        $eventManager,
        SerializerInterface $serializer
    ): JsonResponse
    {
        return new JsonResponse(
            $serializer->serialize([
                'event' => $eventManager->getEvent(
                    $eventId,
                    $this->getUser()
                )], 'json'),
            Response::HTTP_OK,
            [], true
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
        EntityManagerInterface $entityManager,
        SerializerInterface    $serializer
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            return new JsonResponse(
                $serializer->serialize([
                    'errors' => [
                        [
                            'field' => 'event_id',
                            "message" => "Event not found for ID {$eventId}."
                        ]
                    ]
                ],'json'),
                Response::HTTP_NOT_FOUND,
                [],
                true
            );
        }

        if ($event->getStatus()->getId() === Status::CANCELLED) {
            return new JsonResponse(
                $serializer->serialize([
                    'event' => $event
                ],'json'),
                Response::HTTP_OK,
                [],
                true
            );
        }

        $event->setStatus(
            $statusManager->getStatus(Status::CANCELLED)
        );

        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize([
                'event' => $event
            ],'json'),
            Response::HTTP_OK,
            [],
            true
        );
    }

    #[Route(
        "/{eventId}/visibility",
        methods: ["PATCH"]
    )]
    public function makeEventPublic(
        int                    $eventId,
        EventManager           $eventManager,
        EntityManagerInterface $entityManager,
        Request                $request,
        ValidatorInterface     $validator,
        SerializerInterface    $serializer
    ): JsonResponse
    {
        $event = $eventManager->getEvent(
            $eventId,
            $this->getUser()
        );

        if (!$event instanceof Event) {
            return new JsonResponse(
                $serializer->serialize([
                    'errors' => [
                        [
                            'field' => 'event_id',
                            "message" => "Event not found for ID {$eventId}."
                        ]
                    ]
                ],'json'),
                Response::HTTP_NOT_FOUND,
                [],
                true
            );
        }

        $constraints = new Assert\Collection([
            'is_private' => new Assert\Type('boolean'),
        ]);

        $violations = $validator->validate(
            $request->getPayload()->all()
            ,$constraints
        );

        if (count($violations) > 0) {
            $errors = array_map(function ($violation) {
                return [
                    'field' => trim($violation->getPropertyPath(), '[]'),
                    'message' => $violation->getMessage(),
                ];
            },[...$violations]);

            return new JsonResponse(
                $serializer->serialize(['errors' => $errors], 'json'),
                Response::HTTP_BAD_REQUEST,
                [],true
            );
        }

        if ($event->isPrivate() === $request->getPayload()->get( 'is_private')) {
            return new JsonResponse(
                $serializer->serialize(['event' => $event], 'json'),
                Response::HTTP_OK,
                [], true
            );
        }

        $event->setPrivate((bool)$request->getPayload()->get('is_private'));

        $entityManager->flush();

        return new JsonResponse(
            $serializer->serialize(['event' => $event], 'json'),
            Response::HTTP_OK,
            [], true
        );
    }
}