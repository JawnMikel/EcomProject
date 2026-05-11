<?php

declare(strict_types=1);

namespace App\Models;

class BodyWeightEntryModel extends BaseModel
{
    public function getRecentByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, entry_date, weight_value
             FROM body_weight_entries
             WHERE user_id = ?
             ORDER BY entry_date DESC
             LIMIT 10'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function addEntry(int $userId, string $entryDate, float $weightValue): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO body_weight_entries (user_id, entry_date, weight_value)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$userId, $entryDate, $weightValue]);
        return (int) $this->db->lastInsertId();
    }

    public function getLatestWeight(int $userId): ?float
    {
        $stmt = $this->db->prepare(
            'SELECT weight_value FROM body_weight_entries
             WHERE user_id = ?
             ORDER BY entry_date DESC
             LIMIT 1'
        );
        $stmt->execute([$userId]);
        $result = $stmt->fetch();
        return $result ? (float) $result['weight_value'] : null;
    }

    public function getTrend(int $userId, int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            'SELECT entry_date, weight_value
             FROM body_weight_entries
             WHERE user_id = ?
             ORDER BY entry_date DESC
             LIMIT ?'
        );
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        return array_reverse($rows);
    }
}
