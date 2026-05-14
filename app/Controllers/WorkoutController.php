<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\WorkoutSessionModel;
use App\Models\ExerciseModel;
use App\Models\TrainingProgramModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class WorkoutController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig $view,
        private WorkoutSessionModel $workoutModel,
        private ExerciseModel $exerciseModel,
        private TrainingProgramModel $programModel
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $sessions = $this->workoutModel->getRecentByUser((int) $userId);
        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        $programs = $this->programModel->getByUser((int) $userId);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return $this->view->render($response, 'dashboard/workouts.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'Workouts',
            'sessions' => $sessions,
            'active_session' => $activeSession,
            'programs' => $programs,
            'success' => $flashSuccess,
            'error' => $flashError,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $date = trim($data['session_date'] ?? '');
        $duration = trim($data['duration_minutes'] ?? '');
        $volume = trim($data['total_volume'] ?? '');
        $notes = trim($data['notes'] ?? '');

        if (!$date || !$duration) {
            $_SESSION['flash_error'] = 'Please provide session date and duration.';
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['flash_error'] = 'Invalid date format. Use YYYY-MM-DD.';
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $durationMinutes = (int) $duration;
        if ($durationMinutes <= 0) {
            $_SESSION['flash_error'] = 'Duration must be greater than 0 minutes.';
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $volumeValue = $volume !== '' ? (float) $volume : null;

        try {
            $this->workoutModel->createSession(
                (int) $_SESSION['user_id'],
                $date,
                $durationMinutes,
                $volumeValue,
                $notes
            );
            $_SESSION['flash_success'] = 'Workout session logged successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to log workout session. Please try again.';
        }

        return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
    }

    public function start(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $existingActive = $this->workoutModel->getActiveSession((int) $userId);
        if ($existingActive) {
            return $response->withHeader('Location', $this->basePath . '/workouts/active')->withStatus(302);
        }

        $data = (array) $request->getParsedBody();
        $programWorkoutId = !empty($data['program_workout_id']) ? (int) $data['program_workout_id'] : null;

        $sessionId = $this->workoutModel->startSession((int) $userId, $programWorkoutId);

        // If starting from a plan, add exercises from that workout
        if ($programWorkoutId) {
            $planExercises = $this->programModel->getWorkoutExercises($programWorkoutId);
            foreach ($planExercises as $ex) {
                $this->workoutModel->addExercise($sessionId, (int) $ex['id']);
            }
        }

        return $response->withHeader('Location', $this->basePath . '/workouts/active')->withStatus(302);
    }

    public function active(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if (!$activeSession) {
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $exercises = $this->workoutModel->getSessionExercises((int) $activeSession['id']);
        $allExercises = $this->exerciseModel->getAll();
        $categories = $this->exerciseModel->getCategories();

        $exerciseDetails = [];
        foreach ($exercises as $ex) {
            $exerciseDetails[$ex['id']] = [
                'info' => $ex,
                'sets' => $this->workoutModel->getExerciseSets((int) $activeSession['id'], (int) $ex['id'])
            ];
        }

        return $this->view->render($response, 'dashboard/workout-active.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'Active Workout',
            'session' => $activeSession,
            'exercises' => $exercises,
            'exercise_details' => $exerciseDetails,
            'all_exercises' => $allExercises,
            'categories' => $categories,
        ]);
    }

    public function addExercise(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if (!$activeSession) {
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $data = (array) $request->getParsedBody();
        $exerciseId = (int) ($data['exercise_id'] ?? 0);

        if ($exerciseId > 0) {
            $this->workoutModel->addExercise((int) $activeSession['id'], $exerciseId);
        }

        return $response->withHeader('Location', $this->basePath . '/workouts/active')->withStatus(302);
    }

    public function addExerciseGet(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if (!$activeSession) {
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $exerciseId = (int) ($request->getQueryParams()['exercise_id'] ?? 0);

        if ($exerciseId > 0) {
            $this->workoutModel->addExercise((int) $activeSession['id'], $exerciseId);
        }

        return $response->withHeader('Location', $this->basePath . '/workouts/active')->withStatus(302);
    }

    public function addSet(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if (!$activeSession) {
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $data = (array) $request->getParsedBody();
        $exerciseId = (int) ($data['exercise_id'] ?? 0);
        $reps = (int) ($data['reps'] ?? 0);
        $weight = (float) ($data['weight'] ?? 0);

        if ($exerciseId > 0 && $reps > 0) {
            $this->workoutModel->addSet((int) $activeSession['id'], $exerciseId, $reps, $weight);
        }

        return $response->withHeader('Location', $this->basePath . '/workouts/active')->withStatus(302);
    }

    public function deleteSet(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if (!$activeSession) {
            return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
        }

        $data = (array) $request->getParsedBody();
        $setId = (int) ($data['set_id'] ?? 0);

        if ($setId > 0) {
            $this->workoutModel->deleteSet($setId);
        }

        return $response->withHeader('Location', $this->basePath . '/workouts/active')->withStatus(302);
    }

    public function complete(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if ($activeSession) {
            $this->workoutModel->completeSession((int) $activeSession['id']);
            $_SESSION['flash_success'] = 'Workout completed! Great job!';
        }

        return $response->withHeader('Location', $this->basePath . '/workouts')->withStatus(302);
    }
}
