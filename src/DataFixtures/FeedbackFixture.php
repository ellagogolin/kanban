<?php

namespace App\DataFixtures;

use App\Entity\Feedback;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class FeedbackFixture extends Fixture implements DependentFixtureInterface
{

    public function getDependencies()
    {
        return [UserFixture::class];
    }

    public function load(ObjectManager $manager)
    {
        $faker = \Faker\Factory::create();
        for ($i = 1; $i <= 5; $i++) {
            $feedback = new Feedback();
            $feedback->setUser($this->getReference('user-' . $i, User::class));
            $feedback->setMessage($faker->text(30));

            $manager->persist($feedback);
        }
        $manager->flush();
    }
}