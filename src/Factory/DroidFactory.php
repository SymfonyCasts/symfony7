<?php

namespace App\Factory;

use App\Entity\Droid;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Droid>
 */
final class DroidFactory extends PersistentProxyObjectFactory
{
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
        return Droid::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->randomElement([
                'R2-D2', 'C-3PO', 'BB-8', 'ZZZ-123',
            ]),
            'primaryFunction' => self::faker()->randomElement([
                'astromech',
                'protocol',
                'astromech',
                'assassin',
                'sleeper',
            ]),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Droid $droid): void {})
        ;
    }
}
