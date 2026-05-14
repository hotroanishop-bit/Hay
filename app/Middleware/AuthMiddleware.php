<?php
/**
 * Auth Middleware
 * Checks if user is authenticated and redirects to login if not
 */

class AuthMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the request
     * Checks if user is authenticated, redirects to login if not
     *
     * @param callable|null $next The next handler in the chain
     * @return bool|mixed Returns false if not authenticated, or result of next handler
     */
    public function handle(?callable $next = null): mixed
    {
        // Check if user is authenticated
        if (!$this->authService->check()) {
            $this->redirectToLogin();
            return false;
        }

        // User is authenticated, continue to next handler
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
        // Store intended URL for redirect after login
        if (isset($_SERVER['REQUEST_URI'])) {
            $_SESSION['intended_url'] = $_SERVER['REQUEST_URI'];
        }

        header('Location: /login');
        exit;
    }
}
