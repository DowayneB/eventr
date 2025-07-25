<?php

namespace App\Controller\EventType;

use App\Controller\EventrController;
use App\Manager\EventTypeManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    "/api/event-type",
    name: "event_type_main"
)]
class EventTypeController extends EventrController
{
    #[Route('', name: 'api_event_types')]
    public function index(EventTypeManager $eventTypeManager): Response
    {
        return $this->makeSuccessfulResponse([
            'event-type' => $eventTypeManager->getEventTypes()
        ]);
    }

    #[Route('/{event-type-id}', name: 'api_event_type')]
    public function eventType(
        EventTypeManager $eventTypeManager,
        Request          $request,
    ): Response {
        return $this->makeSuccessfulResponse([
            'event-type' => $eventTypeManager->getEventType($this->get($request, $request->attributes->get('event-type-id'))),
        ]);
    }
}
