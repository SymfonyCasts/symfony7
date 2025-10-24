<?php

namespace App\DataFixtures;

use App\Entity\Droid;
use App\Entity\StarshipDroid;
use App\Entity\StarshipStatusEnum;
use App\Factory\DroidFactory;
use App\Factory\StarshipFactory;
use App\Factory\StarshipPartFactory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        StarshipFactory::createOne([
            'name' => 'USS LeafyCruiser (NCC-0001)',
            'class' => 'Garden',
            'captain' => 'Jean-Luc Pickles',
            'status' => StarshipStatusEnum::IN_PROGRESS,
            'arrivedAt' => new \DateTimeImmutable('-1 day'),
        ]);

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

        DroidFactory::createMany(100);

        StarshipFactory::createMany(100, fn() => [
            'droids' => DroidFactory::randomRange(1, 5),
        ]);
        StarshipPartFactory::createMany(100);
    }
}
