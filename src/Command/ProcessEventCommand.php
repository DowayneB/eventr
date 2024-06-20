<?php

namespace App\Command;

use App\Entity\Status;
use App\Manager\EventManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'process-event-command',
    description: 'Add a short description for your command',
)]
class ProcessEventCommand extends Command
{
    private EventManager $eventManager;

    public function __construct(EventManager $eventManager)
    {
        $this->eventManager = $eventManager;
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription(
            "Command Polls non complete and non cancelled events to perform specific actions"
        );
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ): int
    {
        $events = $this->getEventManager()->findIncompleteEvents();

        foreach ($events as $event)
        {
            if (!in_array($event->getStatus()->getId(),Status::INTERNAL_STATUS_FLOW) || $event->isPrivate())
            {
                continue;
            }

            switch ($event->getStatus()->getId())
            {
                case Status::STARTED:
                    $this->sendRSVPNotifications($event);
                    // send invitations to guests, via SMS or email
                    // if guest_id is user_id it would allow for users to manage all events they're invited to
                    // might also be good to help suppliers keep tabs on guest list, and make preparations for dietry requirements
                    // but they also might not have users, so maybe create a user with status GUEST_ONLY until they sign up
                    // send reminders once a week if not RSVP'd and not notifications stopped
                    // send reminder 1 week and 1 day before event for RSVP'd users
                    break;
                case Status::RSVP_CLOSED:
                    $this->sendRsvpClosedNotifications($event);
                    // send RSVP closed emails, and alert event owner that RSVP is closd, and stats on who has RSVP'd
                    break;
                case Status::IN_PROGRESS:
                    $this->sendInProgressNotification($event);
                    // if the event is in progress, send an optional notification. i.e. "the floor on the east side of the building is slippery due to rain etc."
                    // this is sent only once per guest
                case Status::CANCELLED:
                    $this->sendCancellationNotification($event);
                    // send notifications that event has been cancelled.
                    // good opportunity to make a notifications table to not spam people.
                    break;
            }
        }

        return Command::SUCCESS;
    }

    private function getEventManager(): EventManager
    {
        return $this->eventManager;
    }
}
