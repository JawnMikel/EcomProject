<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class OnboardingController
{
    private string $basePath = '/EcomProject/public';

    public function __construct(
        private Twig      $view,
        private UserModel $userModel,
    ) {}

    public function showOnboarding(Request $request, Response $response): Response
    {
        // Must be logged in
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        // If already completed, go to dashboard
        $user = $this->userModel->findById((int) $_SESSION['user_id']);
        if ($user && !empty($user['profile_completed'])) {
            return $response->withHeader('Location', $this->basePath . '/dashboard')->withStatus(302);
        }

        return $this->view->render($response, 'auth/onboarding.twig', [
            'error' => $_SESSION['flash_error'] ?? null,
        ]);
    }

    public function saveOnboarding(Request $request, Response $response): Response
    {
        unset($_SESSION['flash_error']);

        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', $this->basePath . '/login')->withStatus(302);
        }

        $data   = (array) $request->getParsedBody();
        $height = (int) ($data['height_cm'] ?? 0);
        $weight = (float) ($data['weight_kg'] ?? 0);
        $gender = $data['gender'] ?? '';
        $goal   = $data['fitness_goal'] ?? '';
        $activity = $data['activity_level'] ?? '';
        $experience = $data['experience_level'] ?? '';

        $error = $this->validateOnboarding($height, $weight, $gender, $goal, $activity, $experience);
        if ($error) {
            $_SESSION['flash_error'] = $error;
            return $response->withHeader('Location', $this->basePath . '/onboarding')->withStatus(302);
        }

        $userId = (int) $_SESSION['user_id'];
        $this->userModel->saveProfile($userId, $height, $gender, $goal, $activity, $experience);

        // Also create the first body weight entry
        $this->userModel->addBodyWeightEntry($userId, $weight, date('Y-m-d'));

        return $response->withHeader('Location', $this->basePath . '/dashboard')->withStatus(302);
    }

    private function validateOnboarding(
        int $height,
        float $weight,
        string $gender,
        string $goal,
        string $activity,
        string $experience
    ): ?string {
        if ($height < 50 || $height > 300) {
            return 'Please enter a valid height in centimeters (50–300).';
        }
        if ($weight < 20 || $weight > 500) {
            return 'Please enter a valid weight in kilograms (20–500).';
        }
        if (!in_array($gender, ['male', 'female', 'other', 'prefer_not_to_say'], true)) {
            return 'Please select a gender.';
        }
        if (!in_array($goal, ['lose_weight', 'maintain', 'build_muscle', 'increase_endurance'], true)) {
            return 'Please select a fitness goal.';
        }
        if (!in_array($activity, ['sedentary', 'lightly_active', 'moderately_active', 'very_active', 'extremely_active'], true)) {
            return 'Please select an activity level.';
        }
        if (!in_array($experience, ['beginner', 'intermediate', 'advanced'], true)) {
            return 'Please select an experience level.';
        }
        return null;
    }
}
