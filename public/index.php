<?php
<<<<<<< HEAD

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
=======
/**
 * GAINZ - Fitness Tracking Application
 * Entry point for the web application
 */

// Check if this is an API request or web request
$path = $_SERVER['REQUEST_URI'] ?? '/';
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
$accept = $_SERVER['HTTP_ACCEPT'] ?? '';

// If requesting JSON or API endpoints, serve the API
if (strpos($accept, 'application/json') !== false ||
    strpos($path, '/api/') === 0 ||
    (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')) {

    require __DIR__ . '/../vendor/autoload.php';

    use Slim\Factory\AppFactory;
    use Slim\Middleware\ErrorMiddleware;

    // Create app
    $app = AppFactory::create();

    // Add routing middleware
    $app->addRoutingMiddleware();

    // Add error middleware
    $errorMiddleware = $app->addErrorMiddleware(true, true, true);

    // Load environment variables
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();

    // Register routes
    $routes = require __DIR__ . '/../routes.php';
    $routes($app);

    // Run the application
    $app->run();

} else {
    // Serve frontend PHP and static files
    $pathInfo = parse_url($path, PHP_URL_PATH) ?: '/';
    $pathInfo = strtok($pathInfo, '?');

    if ($pathInfo === '/' || $pathInfo === '') {
        $pathInfo = '/login.php';
    }

    $filePath = __DIR__ . $pathInfo;

    if (is_dir($filePath) && file_exists($filePath . '/index.php')) {
        $filePath .= '/index.php';
    }

    if (is_file($filePath)) {
        if (str_ends_with($filePath, '.php')) {
            require $filePath;
            return;
        }

        $mimeType = mime_content_type($filePath) ?: 'text/plain';
        header('Content-Type: ' . $mimeType);
        readfile($filePath);
        return;
    }

    http_response_code(404);
    echo '<h1>Frontend not found</h1><p>Please run the application setup first.</p>';
}
>>>>>>> parent of 7d5d2e8 (cleaning up the main breack)
