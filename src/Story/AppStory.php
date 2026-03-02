<?php

namespace App\Story;

use App\Factory\StarshipFactory;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        StarshipFactory::createMany(3);
    }
}
