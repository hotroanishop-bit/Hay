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

// Load routes
$routesFile = ROOT_PATH . '/routes/web.php';
$routes = [];
if (file_exists($routesFile)) {
    $routes = require $routesFile;
}

/**
 * Simple Router Class
 * Handles route matching, parameter extraction, and middleware execution
 */
class Router
{
    private array $routes = [];
    private AuthService $authService;

    public function __construct(array $routes)
    {
        $this->routes = $routes;
        
        // Initialize AuthService for middleware
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
    }

    /**
     * Match a route and return route info with parameters
     *
     * @param string $method HTTP method
     * @param string $uri Request URI
     * @return array|null Route info with 'route' and 'params' keys, or null if no match
     */
    public function match(string $method, string $uri): ?array
    {
        foreach ($this->routes as $route) {
            // Check method
            if ($route['method'] !== $method) {
                continue;
            }

            // Convert route path to regex pattern
            $pattern = $this->pathToRegex($route['path']);
            
            // Try to match
            if (preg_match($pattern, $uri, $matches)) {
                // Extract named parameters
                $params = $this->extractParams($route['path'], $matches);
                
                return [
                    'route' => $route,
                    'params' => $params
                ];
            }
        }

        return null;
    }

    /**
     * Convert a route path with {param} placeholders to regex
     *
     * @param string $path Route path like /keys/{id}
     * @return string Regex pattern
     */
    private function pathToRegex(string $path): string
    {
        // Escape forward slashes
        $pattern = preg_quote($path, '#');
        
        // Replace {param} with named capture groups
        $pattern = preg_replace('/\\\{([a-zA-Z_][a-zA-Z0-9_]*)\\\}/', '(?P<$1>[^/]+)', $pattern);
        
        return '#^' . $pattern . '$#';
    }

    /**
     * Extract parameters from regex matches
     *
     * @param string $path Original route path
     * @param array $matches Regex matches
     * @return array Associative array of param name => value
     */
    private function extractParams(string $path, array $matches): array
    {
        $params = [];
        
        // Find all {param} in the path
        if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $path, $paramNames)) {
            foreach ($paramNames[1] as $name) {
                if (isset($matches[$name])) {
                    // Convert to int if it's a numeric value
                    $value = $matches[$name];
                    if (is_numeric($value)) {
                        $value = (int)$value;
                    }
                    $params[$name] = $value;
                }
            }
        }
        
        return $params;
    }

    /**
     * Run middleware chain
     *
     * @param array $middlewareClasses Array of middleware class names
     * @return bool True if all middleware passed, false otherwise
     */
    public function runMiddleware(array $middlewareClasses): bool
    {
        foreach ($middlewareClasses as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                error_log("Middleware class not found: $middlewareClass");
                continue;
            }

            // Instantiate middleware with AuthService
            $middleware = new $middlewareClass($this->authService);
            
            // Run middleware handle method
            $result = $middleware->handle();
            
            if ($result === false) {
                // Middleware denied access - it will have handled the response
                return false;
            }
        }

        return true;
    }

    /**
     * Dispatch to a controller action
     *
     * @param string $controllerClass Controller class name
     * @param string $action Method name
     * @param array $params Parameters to pass to the action
     */
    public function dispatch(string $controllerClass, string $action, array $params = []): void
    {
        if (!class_exists($controllerClass)) {
            $this->notFound("Controller not found: $controllerClass");
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->notFound("Action not found: $controllerClass::$action");
            return;
        }

        // Call the action with parameters
        if (!empty($params)) {
            // Pass parameters as arguments in order they appear
            call_user_func_array([$controller, $action], array_values($params));
        } else {
            $controller->$action();
        }
    }

    /**
     * Show 404 Not Found page
     *
     * @param string $message Optional debug message
     */
    public function notFound(string $message = ''): void
    {
        http_response_code(404);
        
        // Check if a custom 404 view exists
        $errorView = VIEWS_PATH . '/errors/404.php';
        if (file_exists($errorView)) {
            include $errorView;
        } else {
            echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; padding: 40px; text-align: center; }
        h1 { color: #333; }
        p { color: #666; }
        a { color: #0066cc; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you are looking for does not exist.</p>
    <a href="/">Return to Home</a>
</body>
</html>';
        }
    }
}

// Create router and process request
$router = new Router($routes);

// Check maintenance mode (skip for login/logout paths to allow admin access)
$skipMaintenancePaths = ['/login', '/logout', '/forgot-password', '/reset-password'];
if (!in_array($requestUri, $skipMaintenancePaths) && !str_starts_with($requestUri, '/reset-password/')) {
    // Initialize services for maintenance check
    $sessionService = new SessionService();
    $userModel = new User();
    $authService = new AuthService($sessionService, $userModel);
    
    // Run maintenance middleware
    $maintenanceMiddleware = new MaintenanceMiddleware($authService);
    if ($maintenanceMiddleware->handle() === false) {
        // Maintenance mode blocked the request - response already sent
        exit;
    }
}

// Match the current request
$match = $router->match($requestMethod, $requestUri);

if ($match === null) {
    // No route matched
    $router->notFound();
    exit;
}

$route = $match['route'];
$params = $match['params'];

// Run middleware if defined
$middleware = $route['middleware'] ?? [];
if (!empty($middleware)) {
    $middlewarePassed = $router->runMiddleware($middleware);
    if (!$middlewarePassed) {
        // Middleware blocked the request - response already sent
        exit;
    }
}

// Dispatch to controller
$router->dispatch($route['controller'], $route['action'], $params);
