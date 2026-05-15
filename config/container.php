<?php

declare(strict_types=1);

use App\Services\EmailService;
use App\Services\TwoFactorService;
use DI\ContainerBuilder;
use Psr\Container\ContainerInterface;
use Slim\Views\Twig;

return function (ContainerBuilder $builder) {
    $builder->addDefinitions([

        'settings' => fn() => require __DIR__ . '/settings.php',

        PDO::class => function (ContainerInterface $c) {
            $db = $c->get('settings')['db'];
            $dsn = "mysql:host={$db['host']};dbname={$db['dbname']};charset={$db['charset']}";
            $pdo = new PDO($dsn, $db['user'], $db['pass']);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $pdo;
        },

        Twig::class => function (ContainerInterface $c) {
            $twig = Twig::create(__DIR__ . '/../app/Views', [
                'cache' => false,
            ]);
            // Make session available in all views
            $twig->getEnvironment()->addGlobal('session', $_SESSION ?? []);
            $twig->getEnvironment()->addGlobal('app_name', $_ENV['APP_NAME'] ?? 'GAINZ');
            $twig->getEnvironment()->addGlobal('app_debug', $_ENV['APP_DEBUG'] ?? false);
            return $twig;
        },

        EmailService::class => fn(ContainerInterface $c) => new EmailService(),

        \App\Models\ExerciseModel::class => function (ContainerInterface $c) {
            return new \App\Models\ExerciseModel($c->get(PDO::class));
        },

        \App\Models\WorkoutSessionModel::class => function (ContainerInterface $c) {
            return new \App\Models\WorkoutSessionModel($c->get(PDO::class));
        },

        \App\Models\TrainingProgramModel::class => function (ContainerInterface $c) {
            return new \App\Models\TrainingProgramModel($c->get(PDO::class));
        },

        TwoFactorService::class => fn(ContainerInterface $c) => new TwoFactorService($c->get(PDO::class)),
        App\Models\UserModel::class => fn(ContainerInterface $c) => new App\Models\UserModel($c->get(PDO::class)),
        App\Models\WorkoutSessionModel::class => fn(ContainerInterface $c) => new App\Models\WorkoutSessionModel($c->get(PDO::class)),
        App\Models\BodyWeightEntryModel::class => fn(ContainerInterface $c) => new App\Models\BodyWeightEntryModel($c->get(PDO::class)),
        App\Models\TrainingProgramModel::class => fn(ContainerInterface $c) => new App\Models\TrainingProgramModel($c->get(PDO::class)),

    ]);
};

