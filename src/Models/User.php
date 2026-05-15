<?php

namespace Gainz\Models;

/**
 * User Model
 * Represents a user in the application
 */
class User
{
    private int $id;
    private string $username;
    private string $email;
    private string $passwordHash;
    private bool $twoFactorEnabled;
    private ?string $twoFactorSecret;
    private \DateTime $birthDate;
    private string $role;
    private \DateTime $createdAt;

    // Extended Profile Fields
    private ?string $bio;
    private ?string $profilePicture;
    private ?string $fitnessGoal;
    private string $experienceLevel;
    private ?float $height;
    private ?float $weightGoal;
    private ?string $workoutFrequency;
    private ?string $preferredDays;
    private ?string $location;

    // Getters
    public function getId(): int
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->twoFactorEnabled;
    }

    public function getTwoFactorSecret(): ?string
    {
        return $this->twoFactorSecret;
    }

    public function getBirthDate(): \DateTime
    {
        return $this->birthDate;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function getBio(): ?string
    {
        return $this->bio;
    }

    public function getProfilePicture(): ?string
    {
        return $this->profilePicture;
    }

    public function getFitnessGoal(): ?string
    {
        return $this->fitnessGoal;
    }

    public function getExperienceLevel(): string
    {
        return $this->experienceLevel;
    }

    public function getHeight(): ?float
    {
        return $this->height;
    }

    public function getWeightGoal(): ?float
    {
        return $this->weightGoal;
    }

    public function getWorkoutFrequency(): ?string
    {
        return $this->workoutFrequency;
    }

    public function getPreferredDays(): ?string
    {
        return $this->preferredDays;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    // Setters
    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setPasswordHash(string $passwordHash): self
    {
        $this->passwordHash = $passwordHash;
        return $this;
    }

    public function setTwoFactorEnabled(bool $twoFactorEnabled): self
    {
        $this->twoFactorEnabled = $twoFactorEnabled;
        return $this;
    }

    public function setTwoFactorSecret(?string $twoFactorSecret): self
    {
        $this->twoFactorSecret = $twoFactorSecret;
        return $this;
    }

    public function setBirthDate(\DateTime $birthDate): self
    {
        $this->birthDate = $birthDate;
        return $this;
    }

    public function setRole(string $role): self
    {
        $this->role = $role;
        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function setBio(?string $bio): self
    {
        $this->bio = $bio;
        return $this;
    }

    public function setProfilePicture(?string $profilePicture): self
    {
        $this->profilePicture = $profilePicture;
        return $this;
    }

    public function setFitnessGoal(?string $fitnessGoal): self
    {
        $this->fitnessGoal = $fitnessGoal;
        return $this;
    }

    public function setExperienceLevel(string $experienceLevel): self
    {
        $this->experienceLevel = $experienceLevel;
        return $this;
    }

    public function setHeight(?float $height): self
    {
        $this->height = $height;
        return $this;
    }

    public function setWeightGoal(?float $weightGoal): self
    {
        $this->weightGoal = $weightGoal;
        return $this;
    }

    public function setWorkoutFrequency(?string $workoutFrequency): self
    {
        $this->workoutFrequency = $workoutFrequency;
        return $this;
    }

    public function setPreferredDays(?string $preferredDays): self
    {
        $this->preferredDays = $preferredDays;
        return $this;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;
        return $this;
    }

    // Utility Methods
    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->passwordHash);
    }

    public function getAge(): int
    {
        $today = new \DateTime('today');
        return $today->diff($this->birthDate)->y;
    }
}