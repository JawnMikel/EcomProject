<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
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
        $group->get('/',          [DashboardController::class, 'index'])->setName('dashboard');
        $group->get('/dashboard', [DashboardController::class, 'index']);
    })->add(AuthMiddleware::class);

};
