<?php

namespace App\DataFixtures;

use App\Entity\Ticket;
use App\Entity\User;
use App\Enum\TicketStatus;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserFixture extends Fixture
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
        $this->setReference('admin', $admin);

        for ($i = 0; $i < 10; $i++) {
            $user = new User();
            $user->setEmail(sprintf('user-%s@example.com', $i));
            $user->setPassword($this->userPasswordHasher->hashPassword($user, 'password'));
            $user->setRoles(['ROLE_USER']);
            $this->setReference(sprintf('user-%s', $i), $user);
            $manager->persist($user);
        }

        $manager->persist($admin);
        $manager->flush();
    }
}
