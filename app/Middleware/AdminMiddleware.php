<?php
/**
 * Admin Middleware
 * Checks if authenticated user has admin privileges and required permissions
 */

class AdminMiddleware
{
    private AuthService $authService;
    private ?PermissionService $permissionService = null;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Get permission service (lazy load)
     */
    private function getPermissionService(): PermissionService
    {
        if ($this->permissionService === null) {
            $this->permissionService = new PermissionService();
        }
        return $this->permissionService;
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

        // Check route-specific permissions
        $requiredPermission = $this->getRequiredPermission();
        if ($requiredPermission && !$this->getPermissionService()->hasPermission($user, $requiredPermission)) {
            $this->denyAccess('You do not have the required permission: ' . $requiredPermission);
            return false;
        }

        // Store permission service in request for controllers
        $_REQUEST['permission_service'] = $this->getPermissionService();

        // User is admin, continue to next handler
        if ($next !== null) {
            return $next();
        }

        return true;
    }

    /**
     * Get required permission for current route
     */
    private function getRequiredPermission(): ?string
    {
        $path = $_SERVER['REQUEST_URI'] ?? '';
        $path = parse_url($path, PHP_URL_PATH);

        // Map routes to required permissions
        $permissionMap = [
            // User management
            '/admin/users' => 'users.view',
            '/admin/users/{id}' => 'users.view',
            '/admin/users/{id}/ban' => 'users.ban',
            '/admin/users/{id}/unban' => 'users.ban',
            '/admin/users/{id}/balance' => 'users.balance',
            '/admin/users/{id}/plan' => 'users.edit',
            
            // Deposit management
            '/admin/deposits' => 'deposits.view',
            '/admin/deposits/{id}' => 'deposits.view',
            '/admin/deposits/{id}/approve' => 'deposits.approve',
            '/admin/deposits/{id}/reject' => 'deposits.reject',
            
            // Ticket management
            '/admin/tickets' => 'tickets.view',
            '/admin/tickets/{id}' => 'tickets.view',
            '/admin/tickets/{id}/reply' => 'tickets.reply',
            '/admin/tickets/{id}/close' => 'tickets.close',
            
            // Settings
            '/admin/settings' => 'settings.view',
            
            // Role management
            '/admin/roles' => 'roles.manage',
            '/admin/roles/create' => 'roles.manage',
            '/admin/roles/{id}/edit' => 'roles.manage',
            '/admin/roles/{id}/delete' => 'roles.manage',
            
            // Impersonation
            '/admin/impersonate/{id}' => 'impersonate',
            '/admin/exit-impersonation' => 'impersonate',
            
            // Health check
            '/admin/health' => 'health.view',
            '/admin/health/refresh' => 'health.view',
            
            // Plans
            '/admin/plans' => 'plans.manage',
            '/admin/plans/create' => 'plans.manage',
            '/admin/plans/{id}/edit' => 'plans.manage',
            '/admin/plans/{id}/update' => 'plans.manage',
            '/admin/plans/{id}/delete' => 'plans.manage',
            
            // Providers
            '/admin/providers' => 'providers.manage',
            '/admin/providers/create' => 'providers.manage',
            '/admin/providers/{id}/edit' => 'providers.manage',
            '/admin/providers/{id}/update' => 'providers.manage',
            '/admin/providers/{id}/delete' => 'providers.manage',
            
            // Coupons
            '/admin/coupons' => 'coupons.manage',
            '/admin/coupons/create' => 'coupons.manage',
            '/admin/coupons/{id}/edit' => 'coupons.manage',
            '/admin/coupons/{id}/update' => 'coupons.manage',
            '/admin/coupons/{id}/delete' => 'coupons.manage',
        ];

        foreach ($permissionMap as $pattern => $permission) {
            if ($this->matchRoute($path, $pattern)) {
                return $permission;
            }
        }

        // Dashboard and other routes don't require specific permissions
        return null;
    }

    /**
     * Match route pattern with current path
     */
    private function matchRoute(string $path, string $pattern): bool
    {
        // Convert pattern to regex
        $regex = preg_replace('/\{[^}]+\}/', '[^/]+', $pattern);
        $regex = '#^' . $regex . '/?$#';
        
        return (bool) preg_match($regex, $path);
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
    private function denyAccess(string $message = 'You do not have permission to access this resource.'): void
    {
        http_response_code(403);
        header('Content-Type: text/html; charset=UTF-8');

        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 Forbidden</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 600px; margin: 100px auto; padding: 20px; text-align: center; }
        h1 { color: #ef4444; }
        p { color: #6b7280; }
        a { color: #6366f1; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <h1>403 Forbidden</h1>
    <p>' . htmlspecialchars($message) . '</p>
    <p><a href="/admin">Return to Admin Dashboard</a> | <a href="/">Return to Home</a></p>
</body>
</html>';
        exit;
    }
}
