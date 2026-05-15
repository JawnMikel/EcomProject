<?php

declare(strict_types=1);

namespace App\Models;

class ExerciseModel extends BaseModel
{
    public function getAll(?string $category = null, ?string $difficulty = null): array
    {
        $sql = '
            SELECT e.id, e.name, e.description, e.difficulty, e.equipment, e.media_url, c.name as category_name
            FROM exercises e
            LEFT JOIN categories c ON e.category_id = c.id
            WHERE 1=1
        ';
        $params = [];

        if ($category) {
            $sql .= ' AND c.name = ?';
            $params[] = $category;
        }

        if ($difficulty) {
            $sql .= ' AND e.difficulty = ?';
            $params[] = $difficulty;
        }

        $sql .= ' ORDER BY c.name, e.name';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function findById(int $id): array|false
    {
        $stmt = $this->db->prepare('
            SELECT e.id, e.name, e.description, e.difficulty, e.equipment, e.media_url, c.name as category_name
            FROM exercises e
            LEFT JOIN categories c ON e.category_id = c.id
            WHERE e.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getCategories(): array
    {
        $stmt = $this->db->query('SELECT id, name FROM categories ORDER BY name');
        return $stmt->fetchAll();
    }

    public function search(string $query): array
    {
        if (empty($query)) {
            $stmt = $this->db->query('SELECT id, name FROM exercises ORDER BY name LIMIT 20');
            return $stmt->fetchAll();
        }
        $stmt = $this->db->prepare(
            'SELECT id, name FROM exercises WHERE name LIKE ? ORDER BY name LIMIT 10'
        );
        $stmt->execute(['%' . $query . '%']);
        return $stmt->fetchAll();
    }

    public function create(string $name, int $categoryId, string $difficulty = 'beginner', string $description = '', string $equipment = ''): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO exercises (category_id, name, description, difficulty, equipment) VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$categoryId, $name, $description, $difficulty, $equipment]);
        return (int) $this->db->lastInsertId();
    }
}