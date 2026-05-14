<?php
/**
 * Health Controller
 * Admin system health monitoring dashboard
 */

class HealthController extends BaseController
{
    private AuthService $authService;
    private HealthCheckService $healthCheckService;
    private PermissionService $permissionService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->healthCheckService = new HealthCheckService();
        $this->permissionService = new PermissionService();
    }

    /**
     * Check if current user is admin with health permission
     */
    private function requireHealthPermission(): bool
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return false;
        }

        if (empty($user['is_admin'])) {
            $this->setFlash('error', 'Access denied. Admin privileges required.');
            $this->redirect('/dashboard');
            return false;
        }

        if (!$this->permissionService->canViewHealth($user)) {
            $this->setFlash('error', 'You do not have permission to view system health.');
            $this->redirect('/admin');
            return false;
        }

        return true;
    }

    /**
     * Display system health dashboard
     * GET /admin/health
     */
    public function index(): void
    {
        if (!$this->requireHealthPermission()) {
            return;
        }

        $metrics = $this->healthCheckService->getFullHealthMetrics();

        $this->currentPage = 'admin-health';
        $this->render('admin/health', [
            'pageTitle' => 'System Health',
            'currentPage' => $this->currentPage,
            'metrics' => $metrics
        ], ['health'], ['health']);
    }

    /**
     * AJAX refresh endpoint for health metrics
     * GET /admin/health/refresh
     */
    public function refresh(): void
    {
        if (!$this->requireHealthPermission()) {
            $this->json(['error' => 'Unauthorized'], 403);
            return;
        }

        $metrics = $this->healthCheckService->getFullHealthMetrics();
        
        $this->json([
            'success' => true,
            'metrics' => $metrics
        ]);
    }
}
