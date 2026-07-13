<?php

namespace App\Dto;

use App\Entity\Starship;
use Symfony\Component\Validator\Constraints as Assert;

final class StarshipPartDto
{
    #[Assert\NotBlank(message: 'Every part should have a name!')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'You forgot to set the price!')]
    public ?int $price = null;

    public ?string $notes = null;

    public ?Starship $starship = null;
}
