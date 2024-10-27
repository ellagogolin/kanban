<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixtures extends Fixture
{
    public function __construct(
        private readonly UserPasswordHasherInterface $userPasswordHasher,

    ) {}

    public function load(ObjectManager $manager): void
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->userPasswordHasher->hashPassword($admin, 'password'));

        $faker = Factory::create();
        $statusCounter = 0;
        for ($i = 0; $i < 10; $i++) {
            $ticket = new Ticket();
            $ticket->author = $admin;
            $ticket->title = $faker->text(60);
            $ticket->description = $faker->text(200);
            if ($i % 2 == 0) {
                $ticket->assignee = $admin;
                $ticket->displayOnKanban = true;
            }
            $ticket->status = TicketStatus::cases()[$statusCounter];
            $statusCounter++;
            if ($statusCounter > 2) {
                $statusCounter = 0;
            }
            $ticket->priority = $faker->numberBetween(0, 3);
            $manager->persist($ticket);
        }


        $manager->persist($admin);
        $manager->flush();
    }
}
