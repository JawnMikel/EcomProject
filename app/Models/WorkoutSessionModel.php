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

    public function startSession(int $userId, ?int $programWorkoutId = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workout_sessions (user_id, start_time, program_workout_id) VALUES (?, NOW(), ?)'
        );
        $stmt->execute([$userId, $programWorkoutId]);
        return (int) $this->db->lastInsertId();
    }

    public function getActiveSession(int $userId): array|false
    {
        $stmt = $this->db->prepare(
            'SELECT id, start_time FROM workout_sessions
             WHERE user_id = ? AND end_time IS NULL
             ORDER BY start_time DESC LIMIT 1'
        );
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }

    public function completeSession(int $sessionId): bool
    {
        $stmt = $this->db->prepare(
            'UPDATE workout_sessions SET end_time = NOW() WHERE id = ?'
        );
        return $stmt->execute([$sessionId]);
    }

    public function addExercise(int $sessionId, int $exerciseId): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workout_session_items (session_id, exercise_id, set_number)
             VALUES (?, ?, 1)'
        );
        $stmt->execute([$sessionId, $exerciseId]);
        return (int) $this->db->lastInsertId();
    }

    public function addSet(int $sessionId, int $exerciseId, int $reps, float $weight): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO workout_session_items (session_id, exercise_id, set_number, reps, weight)
             SELECT ?, ?, COALESCE(MAX(set_number), 0) + 1, ?, ?
             FROM workout_session_items
             WHERE session_id = ? AND exercise_id = ?'
        );
        $stmt->execute([$sessionId, $exerciseId, $reps, $weight, $sessionId, $exerciseId]);
        return (int) $this->db->lastInsertId();
    }

    public function getSessionWithExercises(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT wsi.id, wsi.set_number, wsi.reps, wsi.weight, wsi.set_type,
                    e.id as exercise_id, e.name, e.difficulty, c.name as category_name
             FROM workout_session_items wsi
             JOIN exercises e ON wsi.exercise_id = e.id
             LEFT JOIN categories c ON e.category_id = c.id
             WHERE wsi.session_id = ?
             ORDER BY wsi.id'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    public function getSessionExercises(int $sessionId): array
    {
        $stmt = $this->db->prepare(
            'SELECT DISTINCT e.id, e.name, e.difficulty, c.name as category_name
             FROM workout_session_items wsi
             JOIN exercises e ON wsi.exercise_id = e.id
             LEFT JOIN categories c ON e.category_id = c.id
             WHERE wsi.session_id = ?
             ORDER BY e.name'
        );
        $stmt->execute([$sessionId]);
        return $stmt->fetchAll();
    }

    public function getExerciseSets(int $sessionId, int $exerciseId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, set_number, reps, weight, set_type
             FROM workout_session_items
             WHERE session_id = ? AND exercise_id = ?
             ORDER BY set_number'
        );
        $stmt->execute([$sessionId, $exerciseId]);
        return $stmt->fetchAll();
    }

    public function deleteSet(int $setId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM workout_session_items WHERE id = ?');
        return $stmt->execute([$setId]);
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
