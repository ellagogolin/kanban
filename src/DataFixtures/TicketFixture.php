<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class TicketFixture extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $faker = Factory::create();
        $statusCounter = 0;
        $userCounter = -1;
        for ($i = 0; $i < 40; $i++) {
            $ticket = new Ticket();
            $ticket->author = $this->getReference(sprintf('user-%s', ++$userCounter), User::class);
            $ticket->title = $faker->text(60);
            $ticket->description = $faker->text(200);
            if ($i % 2 == 0) {
                $ticket->assignee = $ticket->author = $this->getReference(sprintf('user-%s', $userCounter), User::class);;
                $ticket->displayOnKanban = true;
            }
            if ($userCounter === 9) {
                $userCounter = -1;
            }
            $ticket->status = TicketStatus::cases()[$statusCounter];
            $statusCounter++;
            if ($statusCounter > 2) {
                $statusCounter = 0;
            }
            $ticket->priority = $faker->numberBetween(0, 3);
            $manager->persist($ticket);
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [UserFixture::class];
    }
}