<?php

namespace Gainz\Models;

/**
 * Workout Model
 * Represents a logged workout session
 */
class Workout
{
    private int $id;
    private int $userId;
    private \DateTime $date;
    private string $name;
    private ?string $notes;
    private int $duration;
    private ?float $bodyweight;
    private array $exercises;
    private \DateTime $createdAt;
    private \DateTime $updatedAt;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getDate(): \DateTime
    {
        return $this->date;
    }

    public function setDate(\DateTime $date): self
    {
        $this->date = $date;
        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;
        return $this;
    }

    public function getDuration(): int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): self
    {
        $this->duration = $duration;
        return $this;
    }

    public function getBodyweight(): ?float
    {
        return $this->bodyweight;
    }

    public function setBodyweight(?float $bodyweight): self
    {
        $this->bodyweight = $bodyweight;
        return $this;
    }

    public function getExercises(): array
    {
        return $this->exercises;
    }

    public function setExercises(array $exercises): self
    {
        $this->exercises = $exercises;
        return $this;
    }

    public function addExercise(array $exercise): self
    {
        $this->exercises[] = $exercise;
        return $this;
    }
}
