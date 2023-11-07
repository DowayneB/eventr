<?php

namespace App\Controller\Events;

use App\Controller\EventrController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route("/event", name: "event_main")]
class EventController extends EventrController
{
    #[Route("/", methods: ['POST'])]
    public function createEvent(Request $request)
    {

    }
}