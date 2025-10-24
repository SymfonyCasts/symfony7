<?php

namespace App\Entity;

use App\Repository\DroidRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DroidRepository::class)]
class Droid
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    private ?string $primaryFunction = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrimaryFunction(): ?string
    {
        return $this->primaryFunction;
    }

    public function setPrimaryFunction(string $primaryFunction): static
    {
        $this->primaryFunction = $primaryFunction;

        return $this;
    }
}
