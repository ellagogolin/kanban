<?php

namespace App\EventListener;

use App\Entity\Ticket;
use App\Entity\TicketLog;
use App\Enum\TicketStatus;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: Ticket::class)]
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Ticket::class)]
#[AsEntityListener(event: Events::preRemove, method: 'preRemove', entity: Ticket::class)]
readonly class TicketChangeListener
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {}

    public function postUpdate(Ticket $ticket, PostUpdateEventArgs $event): void
    {
        $changes = $event->getObjectManager()->getUnitOfWork()->getEntityChangeSet($ticket);
        $previousDisplay = null;
        if (array_key_exists('displayOnKanban', $changes)) {
            $previousDisplay = $changes['displayOnKanban'][0];
        }

        $previousStatus = null;
        if (array_key_exists('status', $changes)) {
            $previousStatus = TicketStatus::from($changes['status'][0]);
        }

        if ($previousStatus !== null || $previousDisplay !== null) {
            $log = new TicketLog();
            $log
                ->setPreviousStatus($previousStatus ?? $ticket->status)
                ->setUpdatedStatus($ticket->status)
                ->setPreviousShowOnKanban($previousDisplay ?? $ticket->displayOnKanban)
                ->setUpdatedShowOnKanban($ticket->displayOnKanban)
                ->setDate(new \DateTimeImmutable());

            if ($previousDisplay === false && $ticket->displayOnKanban && $ticket->status !== TicketStatus::DONE) {
                // unfinished ticket was added to the kanban
                $log->setLoadChange(1);
            } elseif ($previousDisplay && !$ticket->displayOnKanban && $ticket->status !== TicketStatus::DONE) {
                // unfinished ticket was removed from the kanban
                $log->setLoadChange(-1);
            } elseif ($ticket->displayOnKanban && $previousStatus !== TicketStatus::DONE && $ticket->status === TicketStatus::DONE) {
                // ticket on kanban was moved from a previous status to done
                $log->setLoadChange(-1);
            } elseif ($ticket->displayOnKanban && $previousStatus === TicketStatus::DONE && $ticket->status !== TicketStatus::DONE) {
                // ticket on kanban was moved from done to a previous status
                $log->setLoadChange(1);
            }

            $ticket->addLog($log);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }

    public function postPersist(Ticket $ticket, PostPersistEventArgs $event): void
    {
        if ($ticket->status !== TicketStatus::DONE && $ticket->displayOnKanban) {
            $log = new TicketLog();
            $log->setDate(new \DateTimeImmutable())
                ->setTicket($ticket)
                ->setPreviousStatus($ticket->status)
                ->setUpdatedStatus($ticket->status)
                ->setPreviousShowOnKanban($ticket->displayOnKanban)
                ->setUpdatedShowOnKanban($ticket->displayOnKanban)
                ->setLoadChange(1);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }

    public function preRemove(Ticket $ticket, PreRemoveEventArgs $args): void
    {
        foreach ($ticket->getLog() as $log) {
            $log->setTicket(null);
        }
        if ($ticket->status !== TicketStatus::DONE && $ticket->displayOnKanban) {
            $log = new TicketLog();
            $log->setDate(new \DateTimeImmutable())
                ->setPreviousStatus($ticket->status)
                ->setUpdatedStatus($ticket->status)
                ->setPreviousShowOnKanban($ticket->displayOnKanban)
                ->setUpdatedShowOnKanban($ticket->displayOnKanban)
                ->setLoadChange(-1);
            $this->entityManager->persist($log);
            $this->entityManager->flush();
        }
    }
}