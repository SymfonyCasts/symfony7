<?php

namespace App\Entity;

use App\Repository\DroidRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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

    /**
     * @var Collection<int, StarshipDroid>
     */
    #[ORM\OneToMany(targetEntity: StarshipDroid::class, mappedBy: 'droid')]
    private Collection $starshipDroids;

    public function __construct()
    {
        $this->starshipDroids = new ArrayCollection();
    }

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

    /**
     * @return Collection<int, Starship>
     */
    public function getStarships(): Collection
    {
        return $this->starships;
    }

    public function addStarship(Starship $starship): static
    {
        if (!$this->starships->contains($starship)) {
            $this->starships->add($starship);
            $starship->addDroid($this);
        }

        return $this;
    }

    public function removeStarship(Starship $starship): static
    {
        if ($this->starships->removeElement($starship)) {
            $starship->removeDroid($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, StarshipDroid>
     */
    public function getStarshipDroids(): Collection
    {
        return $this->starshipDroids;
    }

    public function addStarshipDroid(StarshipDroid $starshipDroid): static
    {
        if (!$this->starshipDroids->contains($starshipDroid)) {
            $this->starshipDroids->add($starshipDroid);
            $starshipDroid->setDroid($this);
        }

        return $this;
    }

    public function removeStarshipDroid(StarshipDroid $starshipDroid): static
    {
        if ($this->starshipDroids->removeElement($starshipDroid)) {
            // set the owning side to null (unless already changed)
            if ($starshipDroid->getDroid() === $this) {
                $starshipDroid->setDroid(null);
            }
        }

        return $this;
    }
}
