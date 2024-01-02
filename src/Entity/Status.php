<?php

namespace App\Entity;

use App\Repository\StatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatusRepository::class)]
class Status
{
    /**
     * @var int[]
     * This represents the status flow of an event
     */
    const INTERNAL_STATUS_FLOW = [
        self::ACTIVE,
        self::STARTED,
        self::RSVP_CLOSED,
        self::IN_PROGRESS,
        self::COMPLETE
    ];
    const ACTIVE = 2;
    const LOCKED = 3;
    const COMPLETE = 4;
    const CANCELLED = 5;
    const IN_PROGRESS = 6;
    const STARTED = 7;
    const RSVP_CLOSED = 8;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255)]
    private string $slug;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }
}
