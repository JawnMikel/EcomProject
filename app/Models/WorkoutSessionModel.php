<?php

declare(strict_types=1);

namespace App\Models;

class WorkoutSessionModel extends BaseModel
{
    public function getRecentByUser(int $userId): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, start_time, DATE(start_time) AS session_date, end_time,
             COALESCE(total_duration, TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_duration,
             COALESCE(total_volume, (SELECT COALESCE(SUM(reps * weight), 0) FROM workout_session_items WHERE session_id = workout_sessions.id)) as total_volume,
             notes
             FROM workout_sessions
             WHERE user_id = ? AND end_time IS NOT NULL
             ORDER BY start_time DESC
             LIMIT 10'
        );
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function getRecentWithDetails(int $userId): array
    {
        // Get all sessions, with or without exercises
        $stmt = $this->db->prepare(
            'SELECT ws.id, ws.start_time, DATE(ws.start_time) AS session_date, ws.end_time,
             COALESCE(ws.total_duration, TIMESTAMPDIFF(SECOND, ws.start_time, ws.end_time)) as total_duration,
             COALESCE(ws.total_volume, (SELECT COALESCE(SUM(reps * weight), 0) FROM workout_session_items WHERE session_id = ws.id)) as total_volume,
             ws.notes
             FROM workout_sessions ws
             WHERE ws.user_id = ?
             ORDER BY ws.start_time DESC
             LIMIT 10'
        );
        $stmt->execute([$userId]);
        $sessions = $stmt->fetchAll();

        // For each session, get exercises with sets
        foreach ($sessions as &$session) {
            $sessionId = $session['id'];

            // Get exercises grouped with their sets
            $exStmt = $this->db->prepare(
                'SELECT e.id as exercise_id, e.name as exercise_name, c.name as category_name,
                        wsi.id as set_id, wsi.set_number, wsi.reps, wsi.weight, wsi.set_type
                 FROM workout_session_items wsi
                 JOIN exercises e ON wsi.exercise_id = e.id
                 LEFT JOIN categories c ON e.category_id = c.id
                 WHERE wsi.session_id = ?
                 ORDER BY wsi.id'
            );
            $exStmt->execute([$sessionId]);
            $items = $exStmt->fetchAll();

            // Group by exercise
            $exercises = [];
            foreach ($items as $item) {
                if (empty($item['exercise_id'])) continue;
                $exId = $item['exercise_id'];
                if (!isset($exercises[$exId])) {
                    $exercises[$exId] = [
                        'exercise_id' => $item['exercise_id'],
                        'exercise_name' => $item['exercise_name'] ?? 'Unknown Exercise',
                        'category_name' => $item['category_name'] ?? '',
                        'sets' => []
                    ];
                }
                if ($item['set_id'] && $item['reps']) {
                    $exercises[$exId]['sets'][] = [
                        'set_number' => $item['set_number'],
                        'reps' => $item['reps'],
                        'weight' => $item['weight'],
                        'set_type' => $item['set_type']
                    ];
                }
            }
            $session['exercises'] = array_values($exercises);
        }

        return $sessions;
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

    public function getSessionsForMonth(int $userId, int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, start_time, total_duration, total_volume, notes
             FROM workout_sessions
             WHERE user_id = ? AND YEAR(start_time) = ? AND MONTH(start_time) = ?
             ORDER BY start_time DESC'
        );
        $stmt->execute([$userId, $year, $month]);
        return $stmt->fetchAll();
    }

    public function getSessionsWithDetailsForMonth(int $userId, int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, start_time, total_duration, total_volume, notes
             FROM workout_sessions
             WHERE user_id = ? AND YEAR(start_time) = ? AND MONTH(start_time) = ?
             ORDER BY start_time DESC'
        );
        $stmt->execute([$userId, $year, $month]);
        $sessions = $stmt->fetchAll();

        foreach ($sessions as &$session) {
            $sessionId = $session['id'];
            $exStmt = $this->db->prepare(
                'SELECT e.id as exercise_id, e.name as exercise_name, c.name as category_name,
                        wsi.id as set_id, wsi.set_number, wsi.reps, wsi.weight, wsi.set_type
                 FROM workout_session_items wsi
                 JOIN exercises e ON wsi.exercise_id = e.id
                 LEFT JOIN categories c ON e.category_id = c.id
                 WHERE wsi.session_id = ?
                 ORDER BY wsi.id'
            );
            $exStmt->execute([$sessionId]);
            $items = $exStmt->fetchAll();

            $exercises = [];
            foreach ($items as $item) {
                if (empty($item['exercise_id'])) continue;
                $exId = $item['exercise_id'];
                if (!isset($exercises[$exId])) {
                    $exercises[$exId] = [
                        'exercise_id' => $item['exercise_id'],
                        'exercise_name' => $item['exercise_name'] ?? 'Unknown',
                        'category_name' => $item['category_name'] ?? '',
                        'sets' => []
                    ];
                }
                if ($item['set_id'] && $item['reps']) {
                    $exercises[$exId]['sets'][] = [
                        'set_number' => $item['set_number'],
                        'reps' => $item['reps'],
                        'weight' => $item['weight'],
                        'set_type' => $item['set_type']
                    ];
                }
            }
            $session['exercises'] = array_values($exercises);
        }

        return $sessions;
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
        // Calculate total_duration and total_volume
        $calcStmt = $this->db->prepare(
            'SELECT
                TIMESTAMPDIFF(SECOND, start_time, NOW()) as duration_seconds,
                COALESCE(SUM(reps * weight), 0) as volume
             FROM workout_sessions ws
             LEFT JOIN workout_session_items wsi ON ws.id = wsi.session_id
             WHERE ws.id = ?'
        );
        $calcStmt->execute([$sessionId]);
        $result = $calcStmt->fetch();

        $stmt = $this->db->prepare(
            'UPDATE workout_sessions SET end_time = NOW(), total_duration = ?, total_volume = ? WHERE id = ?'
        );
        return $stmt->execute([$result['duration_seconds'], $result['volume'], $sessionId]);
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

    public function updateSet(int $setId, string $field, mixed $value): bool
    {
        $stmt = $this->db->prepare("UPDATE workout_session_items SET $field = ? WHERE id = ?");
        return $stmt->execute([$value, $setId]);
    }

    public function getStatsForUser(int $userId): array
    {
        // Get workout count
        $stmt = $this->db->prepare('SELECT COUNT(*) as count FROM workout_sessions WHERE user_id = ?');
        $stmt->execute([$userId]);
        $totalWorkouts = (int) $stmt->fetch()['count'];

        // Get total duration
        $stmt = $this->db->prepare('SELECT COALESCE(SUM(TIMESTAMPDIFF(SECOND, start_time, end_time)), 0) as total FROM workout_sessions WHERE user_id = ? AND end_time IS NOT NULL');
        $stmt->execute([$userId]);
        $totalDuration = (int) $stmt->fetch()['total'];

        // Get average volume per workout
        $stmt = $this->db->prepare(
            'SELECT COALESCE(AVG(volume_per_workout), 0) as avg_volume FROM (
                SELECT ws.id, COALESCE(SUM(wsi.reps * wsi.weight), 0) as volume_per_workout
                FROM workout_sessions ws
                LEFT JOIN workout_session_items wsi ON ws.id = wsi.session_id
                WHERE ws.user_id = ?
                GROUP BY ws.id
            ) as workout_volumes'
        );
        $stmt->execute([$userId]);
        $averageVolume = (float) $stmt->fetch()['avg_volume'];

        return [
            'total_workouts' => $totalWorkouts,
            'total_duration_seconds' => $totalDuration,
            'average_volume' => $averageVolume,
        ];
    }

    public function getByMonth(int $userId, int $year, int $month): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, start_time, end_time,
             COALESCE(total_duration, TIMESTAMPDIFF(SECOND, start_time, end_time)) as total_duration,
             COALESCE(total_volume, (SELECT COALESCE(SUM(reps * weight), 0) FROM workout_session_items WHERE session_id = workout_sessions.id)) as total_volume,
             notes
             FROM workout_sessions
             WHERE user_id = ? AND YEAR(start_time) = ? AND MONTH(start_time) = ?
             ORDER BY start_time'
        );
        $stmt->execute([$userId, $year, $month]);
        return $stmt->fetchAll();
    }

    public function deleteSession(int $sessionId): bool
    {
        $stmt = $this->db->prepare('DELETE FROM workout_sessions WHERE id = ?');
        return $stmt->execute([$sessionId]);
    }
}
