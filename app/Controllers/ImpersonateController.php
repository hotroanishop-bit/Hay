<?php
/**
 * Impersonate Controller
 * Handles admin user impersonation functionality
 */

class ImpersonateController extends BaseController
{
    private AuthService $authService;
    private AuditService $auditService;
    private User $userModel;
    private AuditLog $auditLogModel;
    private PermissionService $permissionService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        $this->auditService = new AuditService();
        $this->auditLogModel = new AuditLog();
        $this->permissionService = new PermissionService();
    }

    /**
     * Get client IP address
     */
    private function getClientIP(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Start impersonating a user
     * POST /admin/impersonate/{id}
     */
    public function impersonate(int $userId): void
    {
        // Check if user is logged in and is admin
        $admin = $this->authService->user();
        if (!$admin || empty($admin['is_admin'])) {
            $this->setFlash('error', 'Access denied. Admin privileges required.');
            $this->redirect('/login');
            return;
        }

        // Check impersonation permission
        if (!$this->permissionService->canImpersonate($admin)) {
            $this->setFlash('error', 'You do not have permission to impersonate users.');
            $this->redirect('/admin/users');
            return;
        }

        // Get target user
        $targetUser = $this->userModel->find($userId);
        if (!$targetUser) {
            $this->setFlash('error', 'User not found.');
            $this->redirect('/admin/users');
            return;
        }

        // Cannot impersonate another admin
        if (!empty($targetUser['is_admin'])) {
            $this->setFlash('error', 'Cannot impersonate admin users.');
            $this->redirect('/admin/users/' . $userId);
            return;
        }

        // Start impersonation
        if ($this->authService->impersonate($admin['id'], $userId)) {
            // Log the impersonation start
            $this->auditLogModel->logAction(
                $admin['id'],
                'impersonation_started',
                'user',
                $userId,
                null,
                [
                    'target_user_id' => $userId,
                    'target_user_email' => $targetUser['email'],
                    'target_user_name' => $targetUser['name'] ?? 'Unknown'
                ],
                $this->getClientIP()
            );

            $this->setFlash('info', 'You are now viewing as ' . ($targetUser['name'] ?? $targetUser['email']));
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Failed to start impersonation.');
            $this->redirect('/admin/users/' . $userId);
        }
    }

    /**
     * Exit impersonation and return to admin session
     * GET /admin/exit-impersonation
     */
    public function exitImpersonation(): void
    {
        // Check if currently impersonating
        if (!$this->authService->isImpersonating()) {
            $this->setFlash('error', 'You are not currently impersonating anyone.');
            $this->redirect('/dashboard');
            return;
        }

        // Get impersonation info before exiting
        $impersonationInfo = $this->authService->getImpersonationInfo();
        $targetUser = $impersonationInfo['target_user'] ?? null;
        $adminId = $impersonationInfo['admin_id'] ?? null;

        // Exit impersonation
        if ($this->authService->exitImpersonation()) {
            // Log the impersonation end
            if ($adminId && $targetUser) {
                $this->auditLogModel->logAction(
                    $adminId,
                    'impersonation_ended',
                    'user',
                    $targetUser['id'],
                    null,
                    [
                        'target_user_id' => $targetUser['id'],
                        'target_user_email' => $targetUser['email'] ?? 'unknown',
                        'duration_seconds' => time() - ($impersonationInfo['started_at'] ?? time())
                    ],
                    $this->getClientIP()
                );
            }

            $this->setFlash('success', 'You have returned to your admin session.');
            $this->redirect('/admin/users');
        } else {
            $this->setFlash('error', 'Failed to exit impersonation.');
            $this->redirect('/dashboard');
        }
    }
}
