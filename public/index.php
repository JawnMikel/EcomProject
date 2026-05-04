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
