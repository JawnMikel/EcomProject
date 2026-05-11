<?php

declare(strict_types=1);

session_start();

require __DIR__ . '/../vendor/autoload.php';

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Build DI container
$builder = new ContainerBuilder();
(require __DIR__ . '/../config/container.php')($builder);
$container = $builder->build();

// Create Slim app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Add Twig middleware (makes route() available in templates)
$app->add(TwigMiddleware::createFromContainer($app, Twig::class));

// Base path for XAMPP subfolder
$app->setBasePath('/EcomProject/public');

// Routing middleware must be added before error middleware
$app->addRoutingMiddleware();

// Register routes
(require __DIR__ . '/../config/routes.php')($app);

// Add error middleware
$settings = $container->get('settings');
$app->addErrorMiddleware($settings['displayErrorDetails'], true, true);

$app->run();

