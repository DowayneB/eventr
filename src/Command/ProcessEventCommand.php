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
                    // send RSVP emails
                    break;
                case Status::RSVP_CLOSED:
                    // send RSVP closed emails, and alert event owner
                    break;
                case Status::IN_PROGRESS:
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
