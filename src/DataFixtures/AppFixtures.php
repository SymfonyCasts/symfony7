<?php

namespace App\DataFixtures;

use App\Entity\Droid;
use App\Entity\StarshipStatusEnum;
use App\Factory\StarshipFactory;
use App\Factory\StarshipPartFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $starship = StarshipFactory::createOne([
            'name' => 'USS LeafyCruiser (NCC-0001)',
            'class' => 'Garden',
            'captain' => 'Jean-Luc Pickles',
            'status' => StarshipStatusEnum::IN_PROGRESS,
            'arrivedAt' => new \DateTimeImmutable('-1 day'),
        ]);
        $droid1 = new Droid();
        $droid1->setName('IHOP-123');
        $droid1->setPrimaryFunction('Pancake chef');
        $manager->persist($droid1);

        $droid2 = new Droid();
        $droid2->setName('D-3P0');
        $droid2->setPrimaryFunction('C-3PO\'s voice coach');
        $manager->persist($droid2);

        $droid3 = new Droid();
        $droid3->setName('BONK-5000');
        $droid3->setPrimaryFunction('Comedy sidekick');
        $manager->persist($droid3);
        $manager->flush();

        StarshipFactory::createOne([
            'name' => 'USS Espresso (NCC-1234-C)',
            'class' => 'Latte',
            'captain' => 'James T. Quick!',
            'status' => StarshipStatusEnum::COMPLETED,
            'arrivedAt' => new \DateTimeImmutable('-1 week'),
        ]);

        $ship = StarshipFactory::createOne([
            'name' => 'USS Wanderlust (NCC-2024-W)',
            'class' => 'Delta Tourist',
            'captain' => 'Kathryn Journeyway',
            'status' => StarshipStatusEnum::WAITING,
            'arrivedAt' => new \DateTimeImmutable('-1 month'),
        ])->_real();

        $starshipPart = StarshipPartFactory::createOne([
            'name' => 'Toilet Paper',
            'starship' => $ship,
        ])->_real();
        $ship->removePart($starshipPart);
        $manager->flush();

        StarshipFactory::createMany(20);
        StarshipPartFactory::createMany(100);
    }
}
