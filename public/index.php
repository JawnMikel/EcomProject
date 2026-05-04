<?php
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
    // Serve the frontend HTML
    $htmlFile = __DIR__ . '/index.html';
    if (file_exists($htmlFile)) {
        header('Content-Type: text/html');
        readfile($htmlFile);
    } else {
        http_response_code(404);
        echo '<h1>Frontend not found</h1><p>Please run the application setup first.</p>';
    }
}
