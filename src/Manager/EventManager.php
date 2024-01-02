<?php

namespace App\Manager;

use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Status;
use App\Repository\EventRepository;
use Symfony\Component\Security\Core\User\UserInterface;

class EventManager
{
    private EventRepository $eventRepository;
    private StatusManager $statusManager;

    public function __construct(
        EventRepository $eventRepository,
        StatusManager $statusManager
    )
    {
        $this->eventRepository = $eventRepository;
        $this->statusManager = $statusManager;
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

    public function createEvent(
        EventType $eventType,
        \DateTime $eventDate,
        \DateTime $rsvpDate,
        UserInterface $user
    ): Event
    {
        $event = new Event();
        $event->setEventType($eventType);
        $event->setEventDate($eventDate);
        $event->setUser($user);
        $event->setRsvpDate($rsvpDate);
        $event->setStatus(
            $this->getStatusManager()->getStatus(Status::ACTIVE)
        );
        $event->setPrivate(true);

        return $event;
    }

    private function getStatusManager(): StatusManager
    {
        return $this->statusManager;
    }
}