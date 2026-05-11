<?php

declare(strict_types=1);

namespace App\Models;

class TrainingProgramModel extends BaseModel
{
    public function getByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, name, description
             FROM training_programs
             WHERE user_id = ?
             ORDER BY id DESC'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function create(int $userId, string $name, string $description): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO training_programs (user_id, name, description, is_system_template)
             VALUES (?, ?, ?, 0)'
        );
        $stmt->execute([$userId, $name, $description]);
        return (int) $this->db->lastInsertId();
    }

    public function countByUser(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) AS count FROM training_programs WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        $count = $stmt->fetchColumn();
        return (int) $count;
    }
}
