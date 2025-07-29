<?php
// Entry point of the application

// Autoload dependencies
require_once __DIR__ . '/../vendor/autoload.php';

// Simple routing logic
$requestUri = $_SERVER['REQUEST_URI'];
$requestMethod = $_SERVER['REQUEST_METHOD'];

// Basic routing example
if ($requestUri === '/' && $requestMethod === 'GET') {
    echo "Welcome to my PHP project!";
} elseif ($requestUri === '/about' && $requestMethod === 'GET') {
    echo "This is the about page.";
} else {
    http_response_code(404);
    echo "404 Not Found";
}
?>