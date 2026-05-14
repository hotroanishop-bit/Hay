<?php
/**
 * Maintenance Middleware
 * Blocks access for non-admin users when maintenance mode is enabled
 */

class MaintenanceMiddleware
{
    private AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    /**
     * Handle the request
     * Checks if maintenance mode is enabled and blocks non-admin users
     *
     * @param callable|null $next The next handler in the chain
     * @return bool|mixed Returns false if blocked, or result of next handler
     */
    public function handle(?callable $next = null): mixed
    {
        // Check maintenance mode setting
        $settingsService = new SettingsService();
        $maintenanceMode = $settingsService->get('maintenance_mode', '0');

        // If maintenance mode is disabled, allow through
        if ($maintenanceMode !== '1' && $maintenanceMode !== true && $maintenanceMode !== 1) {
            if ($next !== null) {
                return $next();
            }
            return true;
        }

        // Maintenance mode is enabled - check if user is admin
        $user = $this->authService->user();

        // If user is admin, allow through
        if ($user && !empty($user['is_admin'])) {
            if ($next !== null) {
                return $next();
            }
            return true;
        }

        // Block non-admin users and show maintenance page
        $this->showMaintenancePage($settingsService);
        return false;
    }

    /**
     * Display the maintenance page
     */
    private function showMaintenancePage(SettingsService $settingsService): void
    {
        http_response_code(503);
        header('Content-Type: text/html; charset=UTF-8');
        header('Retry-After: 3600');

        // Get site name and maintenance message from settings
        $siteName = $settingsService->get('site_name', 'Hay API Keys');
        $maintenanceMessage = $settingsService->get('maintenance_message', 'We are currently performing scheduled maintenance. Please check back soon.');

        // Include the maintenance view
        $viewPath = VIEWS_PATH . '/errors/maintenance.php';
        if (file_exists($viewPath)) {
            include $viewPath;
        } else {
            // Fallback if view doesn't exist
            echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance Mode - ' . htmlspecialchars($siteName) . '</title>
</head>
<body style="font-family: sans-serif; text-align: center; padding: 50px;">
    <h1>' . htmlspecialchars($siteName) . '</h1>
    <p>' . htmlspecialchars($maintenanceMessage) . '</p>
    <p>We\'ll be back soon!</p>
</body>
</html>';
        }
        exit;
    }
}
