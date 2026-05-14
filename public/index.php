<?php
/**
 * Entry point for the application
 * Handles routing and bootstrapping
 */

// Define path constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', __DIR__);

// Start session
session_start();

// Basic autoloading
spl_autoload_register(function ($class) {
    $paths = [
        APP_PATH . '/Controllers/',
        APP_PATH . '/Models/',
        APP_PATH . '/Services/',
        APP_PATH . '/Middleware/',
    ];
    
    foreach ($paths as $path) {
        $file = $path . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Get the request URI and method
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Remove query string from URI
$requestUri = strtok($requestUri, '?');

// Remove trailing slash (except for root)
if ($requestUri !== '/' && substr($requestUri, -1) === '/') {
    $requestUri = rtrim($requestUri, '/');
}

// Load routes if the file exists
$routesFile = ROOT_PATH . '/routes/web.php';
if (file_exists($routesFile)) {
    require_once $routesFile;
}

// Simple routing logic
// Routes will be defined in routes/web.php and processed here
// For now, provide a basic fallback
if (!isset($routes)) {
    $routes = [];
}

// Match route and dispatch
$routeKey = $requestMethod . ':' . $requestUri;

if (isset($routes[$routeKey])) {
    $handler = $routes[$routeKey];
    if (is_array($handler) && count($handler) === 2) {
        $controllerClass = $handler[0];
        $method = $handler[1];
        $controller = new $controllerClass();
        $controller->$method();
    } elseif (is_callable($handler)) {
        $handler();
    }
} else {
    // 404 Not Found
    http_response_code(404);
    echo '404 Not Found';
}
