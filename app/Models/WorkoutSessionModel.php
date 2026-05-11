<?php

declare(strict_types=1);

namespace App\Models;

class WorkoutSessionModel extends BaseModel
{
    public function getRecentByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, DATE(start_time) AS session_date, total_duration, total_volume, notes
             FROM workout_sessions
             WHERE user_id = ?
             ORDER BY start_time DESC
             LIMIT 10'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function createSession(int $userId, string $date, int $durationMinutes, ?float $volume, ?string $notes): int
    {
        $startTime = sprintf('%s 08:00:00', $date);
        $totalDuration = $durationMinutes * 60;

        $stmt = $this->db->prepare(
            'INSERT INTO workout_sessions (user_id, start_time, total_duration, total_volume, notes)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$userId, $startTime, $totalDuration, $volume, $notes]);
        return (int) $this->db->lastInsertId();
    }

    public function getStatsForUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                COUNT(*) AS total_workouts,
                COALESCE(SUM(total_duration), 0) AS total_duration_seconds,
                COALESCE(AVG(total_volume), 0) AS average_volume
             FROM workout_sessions
             WHERE user_id = ?'
        );
        $stmt->execute([$userId]);
        $stats = $stmt->fetch();
        return [
            'total_workouts' => (int) $stats['total_workouts'],
            'total_duration_seconds' => (int) $stats['total_duration_seconds'],
            'average_volume' => (float) $stats['average_volume'],
        ];
    }
}
