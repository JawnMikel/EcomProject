<?php

declare(strict_types=1);

namespace App\Models;

use App\Core\BaseModel;

class UserModel extends BaseModel
{
    public function findByEmail(string $email): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findByUsername(string $username): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return $stmt->fetch();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(string $username, string $email, string $passwordHash, string $birthDate): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO users (username, email, password_hash, birth_date) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$username, $email, $passwordHash, $birthDate]);
        return (int) $this->db->lastInsertId();
    }

    public function emailExists(string $email): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        return (bool) $stmt->fetch();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->db->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        return (bool) $stmt->fetch();
    }

    public function saveProfile(
        int $userId,
        int $height,
        string $gender,
        string $fitnessGoal,
        string $activityLevel,
        string $experienceLevel
    ): void {
        $stmt = $this->db->prepare(
            'UPDATE users SET
                profile_completed = 1,
                height_cm = ?,
                gender = ?,
                fitness_goal = ?,
                activity_level = ?,
                experience_level = ?
             WHERE id = ?'
        );
        $stmt->execute([$height, $gender, $fitnessGoal, $activityLevel, $experienceLevel, $userId]);
    }

    public function addBodyWeightEntry(int $userId, float $weight, string $entryDate): void
    {
        $stmt = $this->db->prepare(
            'INSERT INTO body_weight_entries (user_id, weight_value, entry_date) VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $weight, $entryDate]);
    }
}
