<?php

use App\Entity\Starship;
use App\Model\StarshipStatusEnum;

$ship1 = new Starship();
$ship1->setName('USS LeafyCruiser (NCC-0001)');
$ship1->setClass('Garden');
$ship1->setCaptain('Jean-Luc Pickles');
$ship1->setStatus(StarshipStatusEnum::IN_PROGRESS);
$ship1->setArrivedAt(new \DateTimeImmutable('-1 day'));

$ship2 = new Starship();
$ship2->setName('USS Espresso (NCC-1234-C)');
$ship2->setClass('Latte');
$ship2->setCaptain('James T. Quick!');
$ship2->setStatus(StarshipStatusEnum::COMPLETED);
$ship2->setArrivedAt(new \DateTimeImmutable('-1 week'));

$ship3 = new Starship();
$ship3->setName('USS Wanderlust (NCC-2024-W)');
$ship3->setClass('Delta Tourist');
$ship3->setCaptain('Kathryn Journeyway');
$ship3->setStatus(StarshipStatusEnum::WAITING);
$ship3->setArrivedAt(new \DateTimeImmutable('-1 month'));
