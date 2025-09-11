<?php

namespace App\Entity;

use App\Repository\StarshipPartRepository;
use App\Repository\StarshipRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Gedmo\Mapping\Annotation\Timestampable;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: StarshipRepository::class)]
class Starship
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?string $name = null;

    #[ORM\Column]
    private ?string $class = null;

    #[ORM\Column]
    private ?string $captain = null;

    #[ORM\Column]
    private ?StarshipStatusEnum $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $arrivedAt = null;

    #[ORM\Column(unique: true)]
    #[Slug(fields: ['name'])]
    private ?string $slug = null;

    /**
     * @var Collection<int, StarshipPart>
     */
    #[ORM\OneToMany(targetEntity: StarshipPart::class, mappedBy: 'starship', fetch: 'EXTRA_LAZY', orphanRemoval: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $parts;

    /**
     * @var Collection<int, StarshipDroid>
     */
    #[ORM\OneToMany(targetEntity: StarshipDroid::class, mappedBy: 'starship', cascade: ['persist'])]
    private Collection $starshipDroids;

    public function __construct()
    {
        $this->parts = new ArrayCollection();
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

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;

        return $this;
    }

    public function getCaptain(): ?string
    {
        return $this->captain;
    }

    public function setCaptain(string $captain): static
    {
        $this->captain = $captain;

        return $this;
    }

    public function getStatus(): ?StarshipStatusEnum
    {
        return $this->status;
    }

    public function setStatus(StarshipStatusEnum $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getArrivedAt(): ?\DateTimeImmutable
    {
        return $this->arrivedAt;
    }

    public function setArrivedAt(\DateTimeImmutable $arrivedAt): static
    {
        $this->arrivedAt = $arrivedAt;

        return $this;
    }

    public function getStatusString(): string
    {
        return $this->status->value;
    }

    public function getStatusImageFilename(): string
    {
        return match ($this->status) {
            StarshipStatusEnum::WAITING => 'images/status-waiting.png',
            StarshipStatusEnum::IN_PROGRESS => 'images/status-in-progress.png',
            StarshipStatusEnum::COMPLETED => 'images/status-complete.png',
        };
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    public function checkIn(?\DateTimeImmutable $arrivedAt = null): static
    {
        $this->arrivedAt = $arrivedAt ?? new \DateTimeImmutable('now');
        $this->status = StarshipStatusEnum::WAITING;

        return $this;
    }

    /**
     * @return Collection<int, StarshipPart>
     */
    public function getParts(): Collection
    {
        return $this->parts;
    }

    /**
     * @return Collection<int, StarshipPart>
     */
    public function getExpensiveParts(): Collection
    {
        return $this->parts->matching(StarshipPartRepository::createExpensiveCriteria());
    }

    public function addPart(StarshipPart $part): static
    {
        if (!$this->parts->contains($part)) {
            $this->parts->add($part);
            $part->setStarship($this);
        }

        return $this;
    }

    public function removePart(StarshipPart $part): static
    {
        if ($this->parts->removeElement($part)) {
            // set the owning side to null (unless already changed)
            if ($part->getStarship() === $this) {
                $part->setStarship(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Droid>
     */
    public function getDroids(): Collection
    {
        return $this->starshipDroids->map(fn (StarshipDroid $starshipDroid) => $starshipDroid->getDroid());
    }

    public function addDroid(Droid $droid, \DateTimeImmutable $assignedAt = null): static
    {
        if (!$this->getDroids()->contains($droid)) {
            $starshipDroid = new StarshipDroid();
            $starshipDroid->setDroid($droid);
            $starshipDroid->setStarship($this);
            if ($assignedAt) {
                $starshipDroid->setAssignedAt($assignedAt);
            }
            $this->starshipDroids->add($starshipDroid);
        }

        return $this;
    }

    public function removeDroid(Droid $droid): static
    {
        $this->droids->removeElement($droid);

        return $this;
    }

    public function getDroidNames(): string
    {
        return implode(', ', $this->getDroids()->map(fn(Droid $droid) => $droid->getName())->toArray());
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
            $starshipDroid->setStarship($this);
        }

        return $this;
    }

    public function removeStarshipDroid(StarshipDroid $starshipDroid): static
    {
        if ($this->starshipDroids->removeElement($starshipDroid)) {
            // set the owning side to null (unless already changed)
            if ($starshipDroid->getStarship() === $this) {
                $starshipDroid->setStarship(null);
            }
        }

        return $this;
    }
}
