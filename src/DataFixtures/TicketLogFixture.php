<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Entity\TicketLog;
use App\Entity\User;
use App\Enum\TicketStatus;
use DateInterval;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TicketLogFixture extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        /** @var TicketLog $logEntry */
        $logEntries = $manager->getRepository(TicketLog::class)->findAll();
        foreach ($logEntries as $logEntry) {
            $logEntry->setDate(\DateTimeImmutable::createFromMutable(date_sub(\DateTime::createFromImmutable($logEntry->getDate()), DateInterval::createFromDateString('7 days'))));
        }
        $manager->flush();

        /** @var Ticket $ticket*/
        $tickets = $manager->getRepository(Ticket::class)->findAll();
        foreach ($tickets as $ticket) {
            if ($ticket->status === TicketStatus::READY && $ticket->displayOnKanban) {
                $ticket->status = TicketStatus::IN_PROGRESS;
            } elseif ($ticket->displayOnKanban && $ticket->status === TicketStatus::IN_PROGRESS) {
                $ticket->status = TicketStatus::DONE;
            }
        }
        $manager->flush();

        /** @var TicketLog $logEntry */
        $logEntries = $manager->getRepository(TicketLog::class)->findBy(['updatedStatus' => TicketStatus::DONE, 'updatedShowOnKanban' => true]);
        foreach ($logEntries as $logEntry) {
            $logEntry->setDate(\DateTimeImmutable::createFromMutable(date_sub(\DateTime::createFromImmutable($logEntry->getDate()), DateInterval::createFromDateString('3 days'))));
        }
        $manager->flush();

        /** @var Ticket $ticket*/
        $tickets = $manager->getRepository(Ticket::class)->findBy(['status' => TicketStatus::IN_PROGRESS, 'displayOnKanban' => true]);
        $counter = 0;
        foreach ($tickets as $ticket) {
            if ($counter %2 === 0) {
                $ticket->status = TicketStatus::DONE;
            }
            $counter++;
        }
        $manager->flush();

        /** @var TicketLog $logEntry */
        $logEntries = $manager->getRepository(TicketLog::class)->findBy(['updatedStatus' => TicketStatus::READY, 'updatedShowOnKanban' => true]);
        foreach ($logEntries as $logEntry) {
            $logEntry->setDate(\DateTimeImmutable::createFromMutable(date_sub(\DateTime::createFromImmutable($logEntry->getDate()), DateInterval::createFromDateString('3 days'))));
        }
        $manager->flush();

        /** @var Ticket $ticket*/
        $tickets = $manager->getRepository(Ticket::class)->findBy(['status' => TicketStatus::READY, 'displayOnKanban' => true]);
        $counter = 0;
        foreach ($tickets as $ticket) {
            if ($counter %3 !== 0) {
                $ticket->status = TicketStatus::DONE;
            }
            $counter++;
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [TicketFixture::class];
    }
}