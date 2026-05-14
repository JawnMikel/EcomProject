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

    public function getWithWorkouts(int $userId): array
    {
        $programs = $this->getByUser($userId);

        // Get all workouts with their exercises
        $stmt = $this->db->prepare(
            'SELECT pw.id, pw.program_id, pw.day_number, pw.workout_name,
                    pwe.id as pwe_id, e.id as exercise_id, e.name as exercise_name,
                    c.name as category_name, pwe.target_sets, pwe.target_reps
             FROM program_workouts pw
             LEFT JOIN program_workout_exercises pwe ON pw.id = pwe.workout_id
             LEFT JOIN exercises e ON pwe.exercise_id = e.id
             LEFT JOIN categories c ON e.category_id = c.id
             WHERE pw.program_id IN (SELECT id FROM training_programs WHERE user_id = ?)
             ORDER BY pw.day_number, pwe.exercise_order'
        );
        $stmt->execute([$userId]);
        $workouts = $stmt->fetchAll();

        $workoutMap = [];
        foreach ($workouts as $w) {
            $pid = $w['program_id'];
            if (!isset($workoutMap[$pid])) {
                $workoutMap[$pid] = [];
            }
            if ($w['exercise_id']) {
                $workoutMap[$pid][] = [
                    'pwe_id' => $w['pwe_id'],
                    'exercise_id' => $w['exercise_id'],
                    'exercise_name' => $w['exercise_name'],
                    'category_name' => $w['category_name'],
                    'target_sets' => $w['target_sets'],
                    'target_reps' => $w['target_reps']
                ];
            }
        }

        foreach ($programs as &$program) {
            $program['workouts'] = $workoutMap[$program['id']] ?? [];
        }

        return $programs;
    }

    public function findById(int $programId): array|false
    {
        $stmt = $this->db->prepare('SELECT * FROM training_programs WHERE id = ?');
        $stmt->execute([$programId]);
        return $stmt->fetch();
    }

    public function create(int $userId, string $name, string $description, string $difficulty = 'beginner', string $environment = 'gym', string $goal = null): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO training_programs (user_id, name, description, is_system_template, difficulty, environment, goal)
             VALUES (?, ?, ?, 0, ?, ?, ?)'
        );
        $stmt->execute([$userId, $name, $description, $difficulty, $environment, $goal]);
        return (int) $this->db->lastInsertId();
    }

    public function getExplorePrograms(?string $goal = null, ?string $difficulty = null, ?string $environment = null): array
    {
        $sql = 'SELECT tp.id, tp.name, tp.description, tp.difficulty, tp.environment, tp.goal,
                       pw.id as workout_id, e.name as exercise_name, c.name as category_name,
                       pwe.target_sets, pwe.target_reps
                FROM training_programs tp
                LEFT JOIN program_workouts pw ON tp.id = pw.program_id
                LEFT JOIN program_workout_exercises pwe ON pw.id = pwe.workout_id
                LEFT JOIN exercises e ON pwe.exercise_id = e.id
                LEFT JOIN categories c ON e.category_id = c.id
                WHERE tp.is_system_template = 1';
        $params = [];

        if ($goal) {
            $sql .= ' AND tp.goal = ?';
            $params[] = $goal;
        }
        if ($difficulty) {
            $sql .= ' AND tp.difficulty = ?';
            $params[] = $difficulty;
        }
        if ($environment) {
            $sql .= ' AND tp.environment = ?';
            $params[] = $environment;
        }

        $sql .= ' ORDER BY tp.id, pw.day_number, pwe.exercise_order';

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Group by program
        $programs = [];
        foreach ($rows as $row) {
            $pid = $row['id'];
            if (!isset($programs[$pid])) {
                $programs[$pid] = [
                    'id' => $row['id'],
                    'name' => $row['name'],
                    'description' => $row['description'],
                    'difficulty' => $row['difficulty'],
                    'environment' => $row['environment'],
                    'goal' => $row['goal'],
                    'workouts' => []
                ];
            }
            if ($row['exercise_name']) {
                $programs[$pid]['workouts'][] = [
                    'exercise_name' => $row['exercise_name'],
                    'category_name' => $row['category_name'],
                    'target_sets' => $row['target_sets'],
                    'target_reps' => $row['target_reps']
                ];
            }
        }

        return array_values($programs);
    }

    public function copyToUser(int $templateId, int $userId): int
    {
        // Get the template
        $stmt = $this->db->prepare('SELECT * FROM training_programs WHERE id = ? AND is_system_template = 1');
        $stmt->execute([$templateId]);
        $template = $stmt->fetch();
        if (!$template) return 0;

        // Create new program for user
        $stmt = $this->db->prepare(
            'INSERT INTO training_programs (user_id, name, description, is_system_template, difficulty, environment, goal)
             VALUES (?, ?, ?, 0, ?, ?, ?)'
        );
        $stmt->execute([$userId, $template['name'], $template['description'], $template['difficulty'], $template['environment'], $template['goal']]);
        $newProgramId = (int) $this->db->lastInsertId();

        // Copy workouts and exercises
        $stmt = $this->db->prepare('SELECT * FROM program_workouts WHERE program_id = ?');
        $stmt->execute([$templateId]);
        $workouts = $stmt->fetchAll();

        foreach ($workouts as $workout) {
            $stmt = $this->db->prepare(
                'INSERT INTO program_workouts (program_id, day_number, workout_name) VALUES (?, ?, ?)'
            );
            $stmt->execute([$newProgramId, $workout['day_number'], $workout['workout_name']]);
            $newWorkoutId = (int) $this->db->lastInsertId();

            $stmt = $this->db->prepare('SELECT * FROM program_workout_exercises WHERE workout_id = ?');
            $stmt->execute([$workout['id']]);
            $exercises = $stmt->fetchAll();

            foreach ($exercises as $ex) {
                $stmt = $this->db->prepare(
                    'INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $stmt->execute([$newWorkoutId, $ex['exercise_id'], $ex['exercise_order'], $ex['target_sets'], $ex['target_reps']]);
            }
        }

        return $newProgramId;
    }

    public function addWorkout(int $programId, int $dayNumber, string $workoutName): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO program_workouts (program_id, day_number, workout_name)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$programId, $dayNumber, $workoutName]);
        return (int) $this->db->lastInsertId();
    }

    public function addExerciseToWorkout(int $workoutId, int $exerciseId, int $order = 0): int
    {
        $stmt = $this->db->prepare(
            'INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order)
             VALUES (?, ?, ?)'
        );
        $stmt->execute([$workoutId, $exerciseId, $order]);
        return (int) $this->db->lastInsertId();
    }

    public function addExerciseToProgram(int $programId, int $exerciseId, int $sets = null, int $reps = null): int
    {
        // Get or create default workout for this program
        $stmt = $this->db->prepare(
            'SELECT id FROM program_workouts WHERE program_id = ? LIMIT 1'
        );
        $stmt->execute([$programId]);
        $workout = $stmt->fetch();

        if ($workout) {
            $workoutId = $workout['id'];
        } else {
            // Create default workout
            $stmt = $this->db->prepare(
                'INSERT INTO program_workouts (program_id, day_number, workout_name)
                 VALUES (?, ?, ?)'
            );
            $stmt->execute([$programId, 1, 'Workout']);
            $workoutId = (int) $this->db->lastInsertId();
        }

        // Get next order
        $stmt = $this->db->prepare(
            'SELECT COALESCE(MAX(exercise_order), 0) + 1 as next_order FROM program_workout_exercises WHERE workout_id = ?'
        );
        $stmt->execute([$workoutId]);
        $nextOrder = $stmt->fetch()['next_order'];

        // Add exercise with sets and reps
        $stmt = $this->db->prepare(
            'INSERT INTO program_workout_exercises (workout_id, exercise_id, exercise_order, target_sets, target_reps)
             VALUES (?, ?, ?, ?, ?)'
        );
        $stmt->execute([$workoutId, $exerciseId, $nextOrder, $sets, $reps]);
        return (int) $this->db->lastInsertId();
    }

    public function updateExercise(int $pweId, int $sets, int $reps): void
    {
        $stmt = $this->db->prepare(
            'UPDATE program_workout_exercises SET target_sets = ?, target_reps = ? WHERE id = ?'
        );
        $stmt->execute([$sets, $reps, $pweId]);
    }

    public function deleteExercise(int $pweId): void
    {
        $stmt = $this->db->prepare('DELETE FROM program_workout_exercises WHERE id = ?');
        $stmt->execute([$pweId]);
    }

    public function deleteProgram(int $programId): void
    {
        $stmt = $this->db->prepare('DELETE FROM training_programs WHERE id = ?');
        $stmt->execute([$programId]);
    }

    public function findOrCreateExercise(string $name, int $categoryId = 1): int
    {
        // Try to find existing exercise
        $stmt = $this->db->prepare('SELECT id FROM exercises WHERE name = ?');
        $stmt->execute([$name]);
        $exercise = $stmt->fetch();

        if ($exercise) {
            return $exercise['id'];
        }

        // Create new exercise
        $stmt = $this->db->prepare(
            'INSERT INTO exercises (category_id, name, difficulty) VALUES (?, ?, ?)'
        );
        $stmt->execute([$categoryId, $name, 'beginner']);
        return (int) $this->db->lastInsertId();
    }

    public function getWorkoutExercises(int $workoutId): array
    {
        $stmt = $this->db->prepare(
            'SELECT pwe.exercise_order, e.id, e.name, e.difficulty, c.name as category_name
             FROM program_workout_exercises pwe
             JOIN exercises e ON pwe.exercise_id = e.id
             LEFT JOIN categories c ON e.category_id = c.id
             WHERE pwe.workout_id = ?
             ORDER BY pwe.exercise_order'
        );
        $stmt->execute([$workoutId]);
        return $stmt->fetchAll();
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
