<?php

namespace App\Story;

use App\Entity\StarshipStatusEnum;
use App\Factory\DroidFactory;
use App\Factory\StarshipFactory;
use App\Factory\StarshipPartFactory;
use App\Factory\UserFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
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
        ]);

        StarshipPartFactory::createOne([
            'name' => 'Toilet Paper',
            'starship' => $ship,
        ]);
        StarshipPartFactory::createMany(100);

        DroidFactory::createMany(100);
        StarshipFactory::createMany(100, fn() => [
            'droids' => DroidFactory::randomRange(1, 5),
        ]);

        UserFactory::createOne([
            'email' => 'user@example.com',
            'firstName' => 'User',
            'password' => '$2y$13$zYGY0mDZZnR5mi7ipHUkqek22.2Xnr2.PLiIA6Q/t0m8afNGTJ/Wq', // hash of "userpass"
        ]);
    }
}
