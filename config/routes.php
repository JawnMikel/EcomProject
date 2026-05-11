<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\WorkoutController;
use App\Controllers\ProgramController;
use App\Controllers\BodyWeightController;
use App\Controllers\AnalyticsController;
use App\Middleware\AuthMiddleware;
use Slim\App;
use Slim\Routing\RouteCollectorProxy;

return function (App $app) {

    // Auth routes (guests only)
    $app->get('/login',    [AuthController::class, 'showLogin'])->setName('login');
    $app->post('/login',   [AuthController::class, 'login']);
    $app->get('/register', [AuthController::class, 'showRegister'])->setName('register');
    $app->post('/register',[AuthController::class, 'register']);
    $app->get('/logout',   [AuthController::class, 'logout'])->setName('logout');

    // Protected routes
    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/',               [DashboardController::class, 'index'])->setName('dashboard');
        $group->get('/dashboard',      [DashboardController::class, 'index']);
        $group->get('/workouts',       [WorkoutController::class, 'index'])->setName('workouts.index');
        $group->post('/workouts',      [WorkoutController::class, 'create'])->setName('workouts.create');
        $group->get('/programs',       [ProgramController::class, 'index'])->setName('programs.index');
        $group->post('/programs',      [ProgramController::class, 'create'])->setName('programs.create');
        $group->get('/analytics',      [AnalyticsController::class, 'index'])->setName('analytics.index');
        $group->get('/body-weight',    [BodyWeightController::class, 'index'])->setName('body_weight.index');
        $group->post('/body-weight',   [BodyWeightController::class, 'create'])->setName('body_weight.create');
    })->add(AuthMiddleware::class);

};
