<?php
/**
 * Test Bootstrap
 * Setup test environment
 */

require __DIR__ . '/../vendor/autoload.php';

// Load test environment
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
if (file_exists(__DIR__ . '/../.env.testing')) {
    $dotenv->load();
} else {
    $dotenv->load();
}

// Set test flag
define('APP_TESTING', true);
