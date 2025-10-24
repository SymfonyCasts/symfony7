<?php

namespace App\Factory;

use App\Entity\StarshipPart;
use App\Entity\StarshipStatusEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<StarshipPart>
 */
final class StarshipPartFactory extends PersistentProxyObjectFactory
{
    private static array $partIdeas = [
        'warp core' => 'looks cool AND zoom',
        'shield generator' => 'in case you run into any Borg',
        'captain\'s chair' => 'just slightly more comfortable than the others',
        'fuzzy dice' => 'obviously',
        'photon torpedoes' => 'for when the fuzzy dice don\'t work',
        'holodeck' => 'parental controls? No way!',
        'Tactical Whoopee Cushion Array' => 'can\'t beat them? Embarrass them!',
        'Temporal Seat Warmers' => 'warm your seat before you sit down',
        'Food Replicator' => 'Earl Grey, hot',
        'Self-Destruct Button Cover' => 'for when you have a cat',
        'Redshirt Dispenser' => 'Instantly replenishes expendable crew members.',
    ];
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return StarshipPart::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        $randomPartKey = self::faker()->randomKey(self::$partIdeas);
        $randomPart = [$randomPartKey, self::$partIdeas[$randomPartKey]];

        return [
            'name' => $randomPart[0],
            'notes' => $randomPart[1],
            'price' => self::faker()->randomNumber(5),
            'starship' => StarshipFactory::randomOrCreate([
                'status' => StarshipStatusEnum::IN_PROGRESS,
            ]),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(StarshipPart $starshipPart): void {})
        ;
    }
}
