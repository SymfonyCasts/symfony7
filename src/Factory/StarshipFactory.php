<?php

namespace App\Factory;

use App\Entity\Starship;
use App\Entity\StarshipStatusEnum;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<Starship>
 */
final class StarshipFactory extends PersistentProxyObjectFactory
{
    private const SHIP_NAMES = [
        'Nebula Drifter',
        'Quantum Voyager',
        'Starlight Nomad',
        'Celestial Serpent',
        'Solar Wanderer',
        'Galactic Mirage',
        'Cosmic Falcon',
        'Nebula Phoenix',
        'Hypernova Explorer',
        'Astro Nomad',
        'Celestial Seeker',
        'Lunar Marauder',
        'Starstruck Dreamer',
        'Void Runner',
        'Orbit Vindicator',
        'Nova Ghost',
        'Sky Raider',
        'Pulsar Rider',
        'Photon Drifter',
        'Astral Nomad',
        'Solar Rogue',
        'Quasar Whisper',
        'Intergalactic Mirage',
        'Nebula Wraith',
        'Gravity Phantom',
        'Stellar Pirate',
        'Comet Streaker',
        'Nebula Zephyr',
        'Celestial Breeze',
        'Event Horizon',
    ];

    private const CLASSES = [
        'Eclipse',
        'Vanguard',
        'Specter',
        'Aurora',
        'Interceptor',
        'Nebula',
        'Corsair',
        'Phoenix',
        'Sentinel',
        'Odyssey',
    ];

    private const CAPTAINS = [
        'Orion Stark',
        'Lyra Voss',
        'Cassian Drake',
        'Zara Rayne',
        'Alaric Forge',
        'Rhea Solaris',
        'Kael Hunter',
        'Luna Seraph',
        'Thorne Valen',
        'Nyx Shadow',
        'Eliar Storm',
        'Vesper Kaine',
        'Astra Starling',
        'Lucian Blaze',
        'Solara Quill',
        'Rowan Steel',
        'Jax Lark',
        'Nova Sinclair',
        'Darius Gale',
        'Lyric Voss',
        'Eir Wen',
        'Silas Kade',
        'Amara Flame',
        'Orion Blackwell',
        'Thalia Star',
        'Cyrus Rook',
        'Sage Aurelius',
        'Zane Frost',
        'Ember Cross',
        'Vale Shadow',
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
        return Starship::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     * @todo add your default values here
     */
    protected function defaults(): array|callable
    {
        return [
            'arrivedAt' => \DateTimeImmutable::createFromMutable(self::faker()->dateTimeBetween('-1 year', 'now')),
            'captain' => self::faker()->randomElement(self::CAPTAINS),
            'class' => self::faker()->randomElement(self::CLASSES),
            'name' => self::faker()->randomElement(self::SHIP_NAMES),
            'status' => self::faker()->randomElement(StarshipStatusEnum::cases()),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Starship $starship): void {})
        ;
    }
}
