<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BodyWeightEntryModel;
use App\Models\TrainingProgramModel;
use App\Models\WorkoutSessionModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class AnalyticsController
{
    public function __construct(
        private Twig $view,
        private WorkoutSessionModel $workoutModel,
        private BodyWeightEntryModel $weightModel,
        private TrainingProgramModel $programModel
    ) {}

    public function index(Request $request, Response $response): Response
    {
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        $stats = $this->workoutModel->getStatsForUser((int) $userId);
        $lastWeight = $this->weightModel->getLatestWeight((int) $userId);
        $weightTrend = $this->weightModel->getTrend((int) $userId, 8);
        $programCount = $this->programModel->countByUser((int) $userId);

        return $this->view->render($response, 'dashboard/analytics.twig', [
            'stats' => $stats,
            'lastWeight' => $lastWeight,
            'weightTrend' => $weightTrend,
            'programCount' => $programCount,
        ]);
    }
}
