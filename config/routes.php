<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\WorkoutController;
use App\Controllers\ProgramController;
use App\Controllers\BodyWeightController;
use App\Controllers\AnalyticsController;
use App\Controllers\ProfileController;
use App\Controllers\ExerciseController;
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
        $group->get('/calendar',       [DashboardController::class, 'calendar'])->setName('calendar');
        $group->get('/workouts',       [WorkoutController::class, 'index'])->setName('workouts.index');
        $group->post('/workouts',      [WorkoutController::class, 'create'])->setName('workouts.create');
        $group->post('/workouts/start', [WorkoutController::class, 'start'])->setName('workouts.start');
        $group->get('/workouts/active', [WorkoutController::class, 'active'])->setName('workouts.active');
        $group->post('/workouts/add-exercise', [WorkoutController::class, 'addExercise'])->setName('workouts.addExercise');
        $group->post('/workouts/add-set', [WorkoutController::class, 'addSet'])->setName('workouts.addSet');
        $group->post('/workouts/delete-set', [WorkoutController::class, 'deleteSet'])->setName('workouts.deleteSet');
        $group->post('/workouts/update-set', [WorkoutController::class, 'updateSet'])->setName('workouts.updateSet');
        $group->post('/workouts/complete', [WorkoutController::class, 'complete'])->setName('workouts.complete');
        $group->get('/programs',       [ProgramController::class, 'index'])->setName('programs.index');
        $group->post('/programs',      [ProgramController::class, 'create'])->setName('programs.create');
        $group->post('/programs/add-workout', [ProgramController::class, 'addWorkout'])->setName('programs.addWorkout');
        $group->post('/programs/add-exercise', [ProgramController::class, 'addExercise'])->setName('programs.addExercise');
        $group->post('/programs/update-exercise', [ProgramController::class, 'updateExercise'])->setName('programs.updateExercise');
        $group->post('/programs/delete-exercise', [ProgramController::class, 'deleteExercise'])->setName('programs.deleteExercise');
        $group->post('/programs/delete', [ProgramController::class, 'deleteProgram'])->setName('programs.delete');
        $group->get('/programs/explore', [ProgramController::class, 'explore'])->setName('programs.explore');
        $group->post('/programs/copy', [ProgramController::class, 'copyProgram'])->setName('programs.copy');
        $group->get('/analytics',      [AnalyticsController::class, 'index'])->setName('analytics.index');
        $group->get('/body-weight',    [BodyWeightController::class, 'index'])->setName('body_weight.index');
        $group->post('/body-weight',   [BodyWeightController::class, 'create'])->setName('body_weight.create');
        $group->get('/exercises',      [ExerciseController::class, 'index'])->setName('exercises.index');
        $group->post('/exercises',     [ExerciseController::class, 'create'])->setName('exercises.create');
        $group->get('/exercises/search', [ExerciseController::class, 'search'])->setName('exercises.search');
        $group->get('/exercises/{id}',  [ExerciseController::class, 'show'])->setName('exercises.show');
        $group->get('/workouts/add-exercise', [WorkoutController::class, 'addExerciseGet'])->setName('workouts.addExerciseGet');
        $group->get('/profile',        [ProfileController::class, 'index'])->setName('profile.index');
        $group->post('/profile',       [ProfileController::class, 'update'])->setName('profile.update');
    })->add(AuthMiddleware::class);

};
