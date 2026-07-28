<?php

namespace App\Dto;

use App\Entity\Starship;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

final class StarshipPartDto
{
    #[Assert\NotBlank(message: 'Every part should have a name!')]
    public ?string $name = null;

    #[Assert\NotBlank(message: 'You forgot to set the price!')]
    #[Assert\GreaterThan(value: 0, message: 'Starship part cannot be free')]
    public ?int $price = null;

    public ?string $notes = null;

    public ?Starship $starship = null;

    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        if ($this->price < 1000 || $this->notes) {
            return;
        }

        $context->buildViolation('Expensive parts must include notes explaining the price')
            ->atPath('notes')
            ->addViolation();
    }
}
