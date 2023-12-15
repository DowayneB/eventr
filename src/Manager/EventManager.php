<?php

namespace App\Manager;

use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class EventManager
{
    private EventRepository $eventRepository;

    public function __construct(EventRepository $eventRepository)
    {
        $this->eventRepository = $eventRepository;
    }

    public function getEvent(int $id, UserInterface $user): ?Event
    {
        return $this->getEventRepository()->findOneBy([
            'id' => $id,
            'user' => $user
        ]);
    }

    private function getEventRepository(): EventRepository
    {
        return $this->eventRepository;
    }

    /**
     * @return Event[]
     */
    public function getEventsByUser(UserInterface $user): array
    {
        return $this->getEventRepository()->findBy([
            'user' => $user
        ]);
    }
}