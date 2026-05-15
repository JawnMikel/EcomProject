<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\TrainingProgramModel;
use App\Models\ExerciseModel;
use App\Models\WorkoutSessionModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class ProgramController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig $view,
        private TrainingProgramModel $programModel,
        private ExerciseModel $exerciseModel,
        private WorkoutSessionModel $workoutModel
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        // Sync start_time from database if there's an active workout
        $activeSession = $this->workoutModel->getActiveSession((int) $userId);
        if ($activeSession && isset($activeSession['start_time']) && empty($_SESSION['start_time'])) {
            $_SESSION['start_time'] = $activeSession['start_time'];
        }

        $programs = $this->programModel->getWithWorkouts((int) $userId);
        $allExercises = $this->exerciseModel->getAll();
        $categories = $this->exerciseModel->getCategories();

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return $this->view->render($response, 'dashboard/programs.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'Training Programs',
            'programs' => $programs,
            'all_exercises' => $allExercises,
            'categories' => $categories,
            'success' => $flashSuccess,
            'error' => $flashError,
        ]);
    }

    public function create(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $name = trim($data['name'] ?? '');
        $description = trim($data['description'] ?? '');

        if (!$name) {
            $_SESSION['flash_error'] = 'Program name cannot be empty.';
            return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
        }

        try {
            $this->programModel->create((int) $_SESSION['user_id'], $name, $description);
            $_SESSION['flash_success'] = 'Program created successfully.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to create program. Please try again.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }

    public function addWorkout(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $programId = (int) ($data['program_id'] ?? 0);
        $dayNumber = (int) ($data['day_number'] ?? 1);
        $workoutName = trim($data['workout_name'] ?? '');

        if (!$programId || !$workoutName) {
            $_SESSION['flash_error'] = 'Please provide workout details.';
            return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
        }

        try {
            $this->programModel->addWorkout($programId, $dayNumber, $workoutName);
            $_SESSION['flash_success'] = 'Workout added to program.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to add workout. Please try again.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }

    public function addExercise(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $programId = (int) ($data['program_id'] ?? 0);
        $exerciseName = trim($data['exercise_name'] ?? '');
        $sets = !empty($data['target_sets']) ? (int) $data['target_sets'] : null;
        $reps = !empty($data['target_reps']) ? (int) $data['target_reps'] : null;

        if (!$programId || !$exerciseName) {
            $_SESSION['flash_error'] = 'Please enter an exercise name.';
            return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
        }

        try {
            // Find or create the exercise
            $exerciseId = $this->programModel->findOrCreateExercise($exerciseName);
            $this->programModel->addExerciseToProgram($programId, $exerciseId, $sets, $reps);
            $_SESSION['flash_success'] = 'Exercise added to routine.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to add exercise. Please try again.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }

    public function updateExercise(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $pweId = (int) ($data['pwe_id'] ?? 0);
        $sets = !empty($data['target_sets']) ? (int) $data['target_sets'] : null;
        $reps = !empty($data['target_reps']) ? (int) $data['target_reps'] : null;

        if (!$pweId) {
            $_SESSION['flash_error'] = 'Invalid exercise.';
            return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
        }

        try {
            $this->programModel->updateExercise($pweId, $sets, $reps);
            $_SESSION['flash_success'] = 'Exercise updated.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to update exercise.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }

    public function deleteExercise(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $pweId = (int) ($data['pwe_id'] ?? 0);

        if (!$pweId) {
            $_SESSION['flash_error'] = 'Invalid exercise.';
            return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
        }

        try {
            $this->programModel->deleteExercise($pweId);
            $_SESSION['flash_success'] = 'Exercise removed.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to remove exercise.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }

    public function deleteProgram(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $programId = (int) ($data['program_id'] ?? 0);

        if (!$programId) {
            $_SESSION['flash_error'] = 'Invalid routine.';
            return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
        }

        try {
            $this->programModel->deleteProgram($programId);
            $_SESSION['flash_success'] = 'Routine deleted.';
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to delete routine.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }

    public function explore(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $query = $request->getQueryParams();
        $goal = $query['goal'] ?? null;
        $difficulty = $query['difficulty'] ?? null;
        $environment = $query['environment'] ?? null;

        $programs = $this->programModel->getExplorePrograms($goal, $difficulty, $environment);
        $allExercises = $this->exerciseModel->getAll();

        return $this->view->render($response, 'dashboard/programs-explore.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'Explore Programs',
            'programs' => $programs,
            'all_exercises' => $allExercises,
            'selected_goal' => $goal,
            'selected_difficulty' => $difficulty,
            'selected_environment' => $environment,
        ]);
    }

    public function copyProgram(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error'], $_SESSION['flash_success']);

        $data = (array) $request->getParsedBody();
        $templateId = (int) ($data['template_id'] ?? 0);

        if (!$templateId) {
            $_SESSION['flash_error'] = 'Invalid program.';
            return $response->withHeader('Location', $this->basePath . '/programs/explore')->withStatus(302);
        }

        try {
            $newId = $this->programModel->copyToUser($templateId, (int) $_SESSION['user_id']);
            if ($newId) {
                $_SESSION['flash_success'] = 'Program added to your routines!';
            } else {
                $_SESSION['flash_error'] = 'Could not copy program.';
            }
        } catch (\Throwable $e) {
            $_SESSION['flash_error'] = 'Unable to copy program.';
        }

        return $response->withHeader('Location', $this->basePath . '/programs')->withStatus(302);
    }
}
