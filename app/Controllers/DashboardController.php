<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\WorkoutSessionModel;
use App\Models\BodyWeightEntryModel;
use App\Models\TrainingProgramModel;
use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController
{
    public function __construct(
        private Twig $view,
        private WorkoutSessionModel $sessionModel,
        private BodyWeightEntryModel $weightModel,
        private TrainingProgramModel $programModel,
        private PDO $db
    ) {}

    public function index(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        $userId = (int) $_SESSION['user_id'];

        // Get stats
        $stats = $this->sessionModel->getStatsForUser($userId);

        // Get workouts this week
        $stmt = $this->db->prepare(
            'SELECT COUNT(*) as count FROM workout_sessions
             WHERE user_id = ? AND start_time >= DATE_SUB(NOW(), INTERVAL 7 DAY)'
        );
        $stmt->execute([$userId]);
        $stats['workouts_this_week'] = (int) $stmt->fetch()['count'];

        // Get current streak (consecutive days with workouts)
        $stats['current_streak'] = $this->calculateStreak($userId);

        // Get recent sessions
        $recentSessions = $this->sessionModel->getRecentByUser($userId);

        // Get weight trend for chart
        $weightTrend = $this->weightModel->getTrend($userId, 10);

        // Calculate min/max for chart scaling
        $weightMin = null;
        $weightMax = null;
        if (!empty($weightTrend)) {
            $weights = array_map(fn($w) => $w['weight_value'], $weightTrend);
            $weightMin = min($weights);
            $weightMax = max($weights);
        }

        // Get total volume lifted (all time)
        $stmt = $this->db->query('SELECT COALESCE(SUM(total_volume), 0) as total FROM workout_sessions WHERE user_id = ' . $userId);
        $stats['total_volume'] = (float) $stmt->fetch()['total'];

        return $this->view->render($response, 'dashboard/index.twig', [
            'username' => $_SESSION['username'],
            'role'     => $_SESSION['user_role'],
            'title'    => 'My Dashboard',
            'stats'    => $stats,
            'recent_sessions' => $recentSessions,
            'weight_trend' => $weightTrend,
            'weight_min' => $weightMin,
            'weight_max' => $weightMax,
        ]);
    }

    private function calculateStreak(int $userId): int
    {
        $stmt = $this->db->prepare(
            'SELECT DATE(start_time) as workout_date FROM workout_sessions
             WHERE user_id = ? GROUP BY DATE(start_time) ORDER BY workout_date DESC'
        );
        $stmt->execute([$userId]);
        $dates = $stmt->fetchAll();

        if (empty($dates)) return 0;

        $streak = 0;
        $today = new \DateTime();
        $today->setTime(0, 0, 0);

        foreach ($dates as $row) {
            $date = new \DateTime($row['workout_date']);
            $date->setTime(0, 0, 0);
            $diff = $today->diff($date)->days;

            if ($diff <= 1) {
                $streak++;
                $today = $date;
            } else {
                break;
            }
        }

        return $streak;
    }
}