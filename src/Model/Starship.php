<?php

namespace App\Model;

class Starship
{
    public function __construct(
        private int $id,
        private string $name,
        private string $class,
        private string $captain,
        private StarshipStatusEnum $status,
    ) {
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getCaptain(): string
    {
        return $this->captain;
    }

    public function getStatus(): StarshipStatusEnum
    {
        return $this->status;
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
}
