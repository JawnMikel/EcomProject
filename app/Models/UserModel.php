<?php

declare(strict_types=1);

namespace App\Models;

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

    public function updateProfile(int $userId, array $data): bool
    {
        $fields = [];
        $values = [];

        $allowedFields = [
            'bio', 'profile_picture', 'fitness_goal', 'experience_level',
            'height', 'weight_goal', 'workout_frequency', 'preferred_days', 'location'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "{$field} = ?";
                $values[] = $data[$field];
            }
        }

        if (empty($fields)) {
            return false;
        }

        $values[] = $userId;
        $sql = 'UPDATE users SET ' . implode(', ', $fields) . ' WHERE id = ?';
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }
}
