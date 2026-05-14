<?php
/**
 * Ban Check Middleware
 * Checks if the current user is banned and logs them out if so
 */

class BanCheckMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the request
     * Checks if current user is banned and logs them out if so
     *
     * @param callable|null $next The next handler in the chain
     * @return bool|mixed Returns false if user is banned, or result of next handler
     */
    public function handle(?callable $next = null): mixed
    {
        // If user is not logged in, allow through
        if (!$this->authService->check()) {
            if ($next !== null) {
                return $next();
            }
            return true;
        }

        // Get current user
        $user = $this->authService->user();

        if (!$user) {
            if ($next !== null) {
                return $next();
            }
            return true;
        }

        // Check if user is banned using User model
        $userModel = new User();
        if ($userModel->isBanned($user['id'])) {
            $this->handleBannedUser();
            return false;
        }

        // User is not banned, continue
        if ($next !== null) {
            return $next();
        }

        return true;
    }

    /**
     * Handle a banned user - set flash message, logout, and redirect
     */
    private function handleBannedUser(): void
    {
        // Set flash message
        $_SESSION['flash'] = [
            'type' => 'error',
            'message' => 'Your account has been suspended. Please contact support.'
        ];

        // Logout the user
        $this->authService->logout();

        // Redirect to login page
        header('Location: /login');
        exit;
    }
}
