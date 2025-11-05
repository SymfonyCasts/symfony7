<?php

namespace App\Story;

use App\Entity\StarshipStatusEnum;
use App\Factory\DroidFactory;
use App\Factory\StarshipFactory;
use App\Factory\StarshipPartFactory;
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
        ])->_real();

        StarshipPartFactory::createOne([
            'name' => 'Toilet Paper',
            'starship' => $ship,
        ]);
        StarshipPartFactory::createMany(100);

        DroidFactory::createMany(100);
        StarshipFactory::createMany(100, fn() => [
            'droids' => DroidFactory::randomRange(1, 5),
        ]);
    }
}
