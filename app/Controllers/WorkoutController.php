<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\WorkoutSessionModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class WorkoutController
{
    public function __construct(
        private Twig $view,
        private WorkoutSessionModel $workoutModel
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        $sessions = $this->workoutModel->getRecentByUser((int) $userId);

        $flashSuccess = $_SESSION['flash_success'] ?? null;
        $flashError = $_SESSION['flash_error'] ?? null;
        unset($_SESSION['flash_success'], $_SESSION['flash_error']);

        return $this->view->render($response, 'dashboard/workouts.twig', [
            'sessions' => $sessions,
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
            return $response->withHeader('Location', '/EcomProject/public/workouts')->withStatus(302);
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            $_SESSION['flash_error'] = 'Invalid date format. Use YYYY-MM-DD.';
            return $response->withHeader('Location', '/EcomProject/public/workouts')->withStatus(302);
        }

        $durationMinutes = (int) $duration;
        if ($durationMinutes <= 0) {
            $_SESSION['flash_error'] = 'Duration must be greater than 0 minutes.';
            return $response->withHeader('Location', '/EcomProject/public/workouts')->withStatus(302);
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

        return $response->withHeader('Location', '/EcomProject/public/workouts')->withStatus(302);
    }
}
