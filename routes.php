<?php
/**
 * GAINZ - Application Routes
 * Define all application routes here
 */

return function ($app) {
    // Home route
    $app->get('/', \Gainz\Controllers\HomeController::class . ':index')
        ->setName('home');

    // User routes
    $app->post('/register', \Gainz\Controllers\AuthController::class . ':register')
        ->setName('register');
    
    $app->post('/login', \Gainz\Controllers\AuthController::class . ':login')
        ->setName('login');
    
    $app->post('/logout', \Gainz\Controllers\AuthController::class . ':logout')
        ->setName('logout');

    // Workout routes
    $app->get('/workouts', \Gainz\Controllers\WorkoutController::class . ':list')
        ->setName('workouts.list');
    
    $app->post('/workouts', \Gainz\Controllers\WorkoutController::class . ':create')
        ->setName('workouts.create');
    
    $app->get('/workouts/{id}', \Gainz\Controllers\WorkoutController::class . ':show')
        ->setName('workouts.show');

    // Exercise routes
    $app->get('/exercises', \Gainz\Controllers\ExerciseController::class . ':list')
        ->setName('exercises.list');
    
    $app->get('/exercises/{id}', \Gainz\Controllers\ExerciseController::class . ':show')
        ->setName('exercises.show');

    // Admin routes
    $app->post('/admin/exercises', \Gainz\Controllers\AdminController::class . ':createExercise')
        ->setName('admin.exercises.create');
    
    $app->post('/admin/programs', \Gainz\Controllers\AdminController::class . ':createProgram')
        ->setName('admin.programs.create');
};
