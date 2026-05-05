<?php

namespace App\Models;

class User
{
    public function __construct(
        public int $id,
        public string $email,
        public string $username,
        public string $dateOfBirth,
        public string $role = 'user',
        public bool $twoFactorEnabled = false,
        public ?string $twoFactorSecret = null,
        public string $createdAt = '',
        public string $updatedAt = ''
    ) {}

    /**
     * Map a RedBeanPHP bean to a typed User object.
     */
    public static function fromBean(object $bean): self
    {
        return new self(
            id: (int) $bean->id,
            email: (string) $bean->email,
            username: (string) $bean->username,
            dateOfBirth: (string) $bean->date_of_birth,
            role: (string) ($bean->role ?? 'user'),
            twoFactorEnabled: (bool) ($bean->two_factor_enabled ?? false),
            twoFactorSecret: $bean->two_factor_secret ? (string) $bean->two_factor_secret : null,
            createdAt: (string) ($bean->created_at ?? ''),
            updatedAt: (string) ($bean->updated_at ?? '')
        );
    }

    /**
     * Check if user is an admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is at least 16 years old
     */
    public function isAtLeast16(): bool
    {
        $birthDate = new \DateTime($this->dateOfBirth);
        $today = new \DateTime();
        $age = $today->diff($birthDate)->y;
        return $age >= 16;
    }

    /**
     * Get user's age
     */
    public function getAge(): int
    {
        $birthDate = new \DateTime($this->dateOfBirth);
        $today = new \DateTime();
        return $today->diff($birthDate)->y;
    }
}