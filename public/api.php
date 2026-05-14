<?php
/**
 * API Entry point for the application
 * Handles API routing and JSON responses
 * Stateless - no session management
 */

// Define path constants
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('VIEWS_PATH', ROOT_PATH . '/views');
define('CONFIG_PATH', ROOT_PATH . '/config');
define('PUBLIC_PATH', __DIR__);

// No session for API - stateless

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

// Set CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With');
header('Content-Type: application/json; charset=utf-8');

// Handle preflight OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

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
$routesFile = ROOT_PATH . '/routes/api.php';
$routes = [];
if (file_exists($routesFile)) {
    $routes = require $routesFile;
}

/**
 * API Router Class
 * Handles route matching, parameter extraction, middleware execution, and JSON responses
 */
class ApiRouter
{
    private array $routes = [];

    public function __construct(array $routes)
    {
        $this->routes = $routes;
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
     * @param string $path Route path like /v1/models/{id}
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
     * Run middleware chain for API routes
     *
     * @param array $middlewareClasses Array of middleware class names
     * @return bool True if all middleware passed, false otherwise
     */
    public function runMiddleware(array $middlewareClasses): bool
    {
        foreach ($middlewareClasses as $middlewareClass) {
            if (!class_exists($middlewareClass)) {
                error_log("API Middleware class not found: $middlewareClass");
                $this->jsonError("Internal server error", "server_error", 500);
                return false;
            }

            // Instantiate middleware (API middleware uses different constructor)
            $middleware = new $middlewareClass();
            
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
            $this->jsonError("Controller not found", "invalid_request_error", 500);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->jsonError("Action not found", "invalid_request_error", 500);
            return;
        }

        // Call the action with parameters
        try {
            if (!empty($params)) {
                // Pass parameters as arguments in order they appear
                call_user_func_array([$controller, $action], array_values($params));
            } else {
                $controller->$action();
            }
        } catch (Throwable $e) {
            error_log("API Error: " . $e->getMessage());
            $this->jsonError("Internal server error", "server_error", 500);
        }
    }

    /**
     * Output JSON error response in OpenAI-compatible format
     *
     * @param string $message Error message
     * @param string $type Error type
     * @param int $statusCode HTTP status code
     */
    public function jsonError(string $message, string $type, int $statusCode = 400): void
    {
        http_response_code($statusCode);
        echo json_encode([
            'error' => [
                'message' => $message,
                'type' => $type,
                'code' => null
            ]
        ], JSON_UNESCAPED_UNICODE);
    }

    /**
     * Show 404 Not Found as JSON
     */
    public function notFound(): void
    {
        $this->jsonError("The requested endpoint does not exist", "invalid_request_error", 404);
    }

    /**
     * Show 500 Internal Server Error as JSON
     */
    public function serverError(): void
    {
        $this->jsonError("Internal server error", "server_error", 500);
    }
}

// Create router and process request
$router = new ApiRouter($routes);

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
