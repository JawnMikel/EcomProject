<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\OnboardingController;
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

    // Onboarding (must be logged in, but not protected by full auth middleware)
    $app->get('/onboarding',  [OnboardingController::class, 'showOnboarding'])->setName('onboarding');
    $app->post('/onboarding', [OnboardingController::class, 'saveOnboarding']);

    // Root redirect: login if guest, dashboard if authenticated
    $app->get('/', function ($request, $response) {
        $target = empty($_SESSION['user_id']) ? '/login' : '/dashboard';
        return $response->withHeader('Location', '/EcomProject/public' . $target)->withStatus(302);
    });

    // Protected routes
    $app->group('', function (RouteCollectorProxy $group) {
        $group->get('/dashboard', [DashboardController::class, 'index'])->setName('dashboard');
    })->add(AuthMiddleware::class);

};
