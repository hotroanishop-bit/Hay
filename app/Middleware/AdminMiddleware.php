<?php
/**
 * Admin Middleware
 * Checks if authenticated user has admin privileges
 */

class AdminMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the request
     * Checks if user is authenticated and is an admin
     * Returns 403 Forbidden if not admin
     *
     * @param callable|null $next The next handler in the chain
     * @return bool|mixed Returns false if not authorized, or result of next handler
     */
    public function handle(?callable $next = null): mixed
    {
        // First check if user is authenticated
        if (!$this->authService->check()) {
            $this->redirectToLogin();
            return false;
        }

        // Get the current user
        $user = $this->authService->user();

        // Check if user is admin
        if (!$user || empty($user['is_admin'])) {
            $this->denyAccess();
            return false;
        }

        // User is admin, continue to next handler
        if ($next !== null) {
            return $next();
        }

        return true;
    }

    /**
     * Redirect to login page
     */
    private function redirectToLogin(): void
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        }

        header('Location: /login');
        exit;
    }

    /**
     * Deny access with 403 Forbidden
     */
    private function denyAccess(): void
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p>You do not have permission to access this resource.</p>
    <a href="/">Return to Home</a>
</body>
</html>';
        exit;
    }
}
