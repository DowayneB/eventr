<?php

namespace App\Manager;

use App\Entity\Event;
use App\Entity\EventType;
use App\Entity\Location;
use App\Entity\Status;
use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
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
    public function getActiveEventsByUser(UserInterface $user): array
    {
        return $this->getEventRepository()->createQueryBuilder('e')
            ->where('e.user = :user')
            ->andWhere('e.status != :cancelled')
            ->setParameter('user', $user)
            ->setParameter('cancelled', Status::CANCELLED)
            ->getQuery()->getResult();

    }

    public function createEvent(
        EventType $eventType,
        string $eventDescription,
        ?string $summary,
        Location $location,
        \DateTime $eventDate,
        \DateTime $endDate,
        \DateTime $rsvpDate,
        UserInterface $user,
        bool $private
    ): Event
    {
        $event = new Event();
        $event->setEventType($eventType);
        $event->setDescription($eventDescription);
        $event->setSummary($summary);
        $event->setLocation($location);
        $event->setEventDate($eventDate);
        $event->setEndDate($endDate);
        $event->setUser($user);
        $event->setRsvpDate($rsvpDate);
        $event->setStatus(
            $this->getStatusManager()->getStatus(Status::ACTIVE)
        );
        $event->setPrivate($private);

        return $event;
    }

    private function getStatusManager(): StatusManager
    {
        return $this->statusManager;
    }

    /**
     * @return Event[]
     */
    public function findIncompleteEvents(): array
    {
        return $this->getEventRepository()->findIncompleteEvents();
    }

   /**
    * @return Event[]
    */
    public function getPublicEventsForUser(?UserInterface $user): array
    {
        return $this->getEventRepository()->createQueryBuilder('e')
            ->where('e.user != :user')
            ->andWhere('e.status != :cancelled')
            ->andWhere('e.private = false')
            ->setParameter('user', $user)
            ->setParameter('cancelled', Status::CANCELLED)
            ->getQuery()->getResult();
    }
}