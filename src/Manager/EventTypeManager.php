<?php

namespace App\Manager;

use App\Entity\EventType;
use App\Repository\EventTypeRepository;

class EventTypeManager
{
    private EventTypeRepository $eventTypeRepository;
    public function __construct(EventTypeRepository $eventTypeRepository)
    {
        $this->eventTypeRepository = $eventTypeRepository;
    }

    /**
     * @return EventType[]
     */
    public function getEventTypes(): array
    {
        return $this->eventTypeRepository->findAll();
    }

    public function getEventType(int $eventTypeId): ?EventType
    {
        return $this->getEventTypeRepository()->find(
            $eventTypeId
        );
    }

    private function getEventTypeRepository(): EventTypeRepository
    {
        return $this->eventTypeRepository;
    }
}