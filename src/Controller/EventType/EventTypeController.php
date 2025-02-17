<?php

namespace App\Controller\EventType;

use App\Controller\EventrController;
use App\Manager\EventTypeManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    "/api/event-type",
    name: "event_type_main"
)]
class EventTypeController extends EventrController
{
    #[Route('', name: 'app_event_type')]
    public function index(EventTypeManager $eventTypeManager): Response
    {
        return $this->makeSerializedResponse([
            'event-type' => $eventTypeManager->getEventTypes()
        ]);
    }
}
