<?php

namespace App\Entity;

use App\Repository\EventRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Timestampable;
use Symfony\Component\Security\Core\User\UserInterface;


#[ORM\Entity(repositoryClass: EventRepository::class)]
class Event
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, unique: false)]
    private EventType $event_type;

    #[ORM\Column(type: Types::STRING)]
    private string $description;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, unique: false)]
    private Status $status;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $private;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $event_date;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $rsvp_date;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(name: 'user_id', nullable: false)]
    private UserInterface $user;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Timestampable(on: 'create')]
    private \DateTimeInterface $created_at;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Timestampable(on: 'update')]
    private \DateTimeInterface $updated_at;

    #[ORM\ManyToMany(targetEntity: Guest::class, mappedBy: 'events')]
    private Collection $guests;

    #[ORM\OneToOne(mappedBy: 'event_id', cascade: ['persist', 'remove'])]
    private ?Invitation $invitation = null;

    public function __construct()
    {
        $this->guests = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getEventDate(): ?\DateTimeInterface
    {
        return $this->event_date;
    }

    public function setEventDate(\DateTimeInterface $event_date): static
    {
        $this->event_date = $event_date;

        return $this;
    }

    public function getRsvpDate(): ?\DateTimeInterface
    {
        return $this->rsvp_date;
    }

    public function setRsvpDate(\DateTimeInterface $rsvp_date): static
    {
        $this->rsvp_date = $rsvp_date;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->created_at;
    }

    public function setCreatedAt(\DateTimeInterface $created_at): static
    {
        $this->created_at = $created_at;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updated_at;
    }

    public function setUpdatedAt(\DateTimeInterface $updated_at): static
    {
        $this->updated_at = $updated_at;

        return $this;
    }

    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getEventType(): EventType
    {
        return $this->event_type;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getStatus(): Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): void
    {
        $this->status = $status;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    public function setEventType(EventType $event_type): static
    {
        $this->event_type = $event_type;

        return $this;
    }

    /**
     * @return Collection<int, Guest>
     */
    public function getGuests(): Collection
    {
        return $this->guests;
    }

    public function addGuest(Guest $guest): static
    {
        if (!$this->guests->contains($guest)) {
            $this->guests->add($guest);
            $guest->addEvent($this);
        }

        return $this;
    }

    public function removeGuest(Guest $guest): static
    {
        if ($this->guests->removeElement($guest)) {
            $guest->removeEvent($this);
        }

        return $this;
    }

    public function getInvitation(): ?Invitation
    {
        return $this->invitation;
    }

    public function setInvitation(Invitation $invitation): static
    {
        // set the owning side of the relation if necessary
        if ($invitation->getEventId() !== $this) {
            $invitation->setEventId($this);
        }

        $this->invitation = $invitation;

        return $this;
    }
}
