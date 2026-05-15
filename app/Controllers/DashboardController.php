<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\BodyWeightEntryModel;
use App\Models\TrainingProgramModel;
use App\Models\WorkoutSessionModel;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Views\Twig;

class DashboardController
{
    public function __construct(
        private Twig $view,
        private WorkoutSessionModel $workoutSessionModel,
        private BodyWeightEntryModel $bodyWeightEntryModel,
        private TrainingProgramModel $trainingProgramModel,
    ) {}

    public function index(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        // Sync start_time from database if there's an active workout
        $activeSession = $this->workoutSessionModel->getActiveSession((int) $_SESSION['user_id']);
        if ($activeSession && isset($activeSession['start_time']) && empty($_SESSION['start_time'])) {
            $_SESSION['start_time'] = $activeSession['start_time'];
        }

        $userId = (int) $_SESSION['user_id'];
        $workoutStats = $this->workoutSessionModel->getStatsForUser($userId);
        $recentWorkouts = $this->workoutSessionModel->getRecentByUser($userId);
        $latestWeight = $this->bodyWeightEntryModel->getLatestWeight($userId);
        $programCount = $this->trainingProgramModel->countByUser($userId);

        $workoutStreak = 0;
        $lastDate = null;
        $latestWorkout = null;

        foreach ($recentWorkouts as $workout) {
            if (empty($workout['session_date'])) {
                continue;
            }

            if ($latestWorkout === null) {
                $latestWorkout = $workout;
            }

            $date = new \DateTime($workout['session_date']);
            if ($lastDate === null) {
                $workoutStreak = 1;
            } else {
                $expected = (clone $lastDate)->modify('-1 day');
                if ($date->format('Y-m-d') === $expected->format('Y-m-d')) {
                    $workoutStreak++;
                } elseif ($date->format('Y-m-d') !== $lastDate->format('Y-m-d')) {
                    break;
                }
            }
            $lastDate = $date;
        }

        $latestWorkoutLabel = 'No workouts logged yet';
        $latestWorkoutBadge = 'Start one now';
        if ($latestWorkout !== null) {
            $latestWorkoutLabel = sprintf('%s — %dm', $latestWorkout['session_date'], (int) round($latestWorkout['total_duration'] / 60));
            $latestWorkoutBadge = 'Latest workout';
        }

        $weightLabel = $latestWeight !== null ? sprintf('%0.1f kg', $latestWeight) : 'No weight recorded';
        $weightHint = $latestWeight !== null
            ? 'Based on your most recent entry.'
            : 'Record your weight to track progress.';

        $summaryText = $workoutStats['total_workouts'] > 0
            ? 'Your dashboard is driven by your own workouts, body weight entries, and active programs.'
            : 'No workout history yet. Start by logging your first session or weight entry.';

        return $this->view->render($response, 'dashboard/index.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'My Dashboard',
            'summaryText' => $summaryText,
            'workoutStreak' => $workoutStreak,
            'totalWorkouts' => $workoutStats['total_workouts'],
            'averageSessionMinutes' => $workoutStats['total_workouts'] > 0 ? (int) round($workoutStats['total_duration_seconds'] / 60 / max(1, $workoutStats['total_workouts'])) : 0,
            'latestWorkoutLabel' => $latestWorkoutLabel,
            'latestWorkoutBadge' => $latestWorkoutBadge,
            'latestWeight' => $weightLabel,
            'weightHint' => $weightHint,
            'programCount' => $programCount,
            'recommendedSupplements' => [
                ['name' => 'Whey Isolate', 'benefit' => 'Fast-absorbing protein support after training.', 'tag' => 'Top Pick'],
                ['name' => 'Creatine Monohydrate', 'benefit' => 'Strength and power support for heavier lifts.', 'tag' => 'Daily'],
                ['name' => 'Omega-3 Fish Oil', 'benefit' => 'Joint recovery and inflammation support.', 'tag' => 'Recovery'],
            ],
            'latestArticles' => [
                ['title' => '5 Recovery Hacks for Heavy Training', 'summary' => 'Use these evidence-backed strategies to stay ready for your next session.', 'date' => 'May 14, 2026', 'url' => 'https://www.bodybuilding.com/content/7-recovery-hacks.html'],
                ['title' => 'Best Snacks for Muscle Growth', 'summary' => 'Smart food choices that support gains without bloating.', 'date' => 'May 12, 2026', 'url' => 'https://www.menshealth.com/nutrition/a19545626/best-protein-snacks/'],
                ['title' => 'How to Stay Hydrated and Energized', 'summary' => 'A quick hydration plan built for athletes on the move.', 'date' => 'May 10, 2026', 'url' => 'https://www.healthline.com/nutrition/how-much-water-should-you-drink-per-day'],
            ],
            'focusTip' => $workoutStats['total_workouts'] > 0
                ? 'You are building real momentum. Keep logging each session and keep your form tight.'
                : 'Get started with your first workout and a body weight entry to make every metric count.',
        ]);
    }

    public function calendar(Request $request, Response $response): Response
    {
        if (empty($_SESSION['user_id'])) {
            return $response->withHeader('Location', '/EcomProject/public/login')->withStatus(302);
        }

        $params = $request->getQueryParams();
        $month = isset($params['month']) ? (int) $params['month'] : (int) date('n');
        $year = isset($params['year']) ? (int) $params['year'] : (int) date('Y');
        $month = max(1, min(12, $month));

        $userId = (int) $_SESSION['user_id'];
        $workouts = $this->workoutSessionModel->getSessionsForMonth($userId, $year, $month);

        return $this->view->render($response, 'dashboard/calendar.twig', [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['user_role'],
            'title' => 'Calendar',
            'year' => $year,
            'month' => $month,
            'workouts' => $workouts,
        ]);
    }
}