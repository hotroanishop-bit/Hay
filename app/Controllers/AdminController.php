<?php
/**
 * Admin Controller
 * Handles admin panel operations
 */

class AdminController extends BaseController
{
    private AuthService $authService;
    private AuditService $auditService;
    private User $userModel;
    private Transaction $transactionModel;
    private Ticket $ticketModel;
    private ApiKey $apiKeyModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        
        $this->transactionModel = new Transaction();
        $this->ticketModel = new Ticket();
        $this->apiKeyModel = new ApiKey();
        $this->auditService = new AuditService();
    }

    /**
     * Check if current user is admin
     */
    private function requireAdmin(): bool
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

        return true;
    }

    /**
     * Show admin dashboard with user list
     */
    public function users(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;

        $users = $this->userModel->getAllUsers($page, $perPage);
        $totalUsers = $this->userModel->count([]);

        // Get ticket counts for admin overview
        $ticketCounts = $this->ticketModel->getAllStatusCounts();

        $this->currentPage = 'admin-users';
        $this->render('admin/users', [
            'pageTitle' => 'Admin - Users',
            'currentPage' => $this->currentPage,
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalUsers / $perPage),
                'total' => $totalUsers,
                'per_page' => $perPage
            ],
            'ticketCounts' => $ticketCounts
        ], ['admin'], ['admin']);
    }

    /**
     * Show user detail
     */
    public function userDetail(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->userModel->find($id);

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/users');
            return;
        }

        // Get user's API keys
        $apiKeys = $this->apiKeyModel->findByUser($id);

        // Get user's transactions
        $transactions = $this->transactionModel->findByUser($id, 20);

        // Get user's tickets
        $tickets = $this->ticketModel->findByUser($id);

        // Get audit log
        $auditLog = $this->auditService->getAuditLog($id, 50);

        $this->currentPage = 'admin-users';
        $this->render('admin/user_detail', [
            'pageTitle' => 'Admin - User Details',
            'currentPage' => $this->currentPage,
            'user' => $user,
            'apiKeys' => $apiKeys,
            'transactions' => $transactions,
            'tickets' => $tickets,
            'auditLog' => $auditLog
        ], ['admin'], ['admin']);
    }

    /**
     * Show all transactions
     */
    public function transactions(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        // Get all transactions (would need a method in Transaction model)
        $transactions = $this->transactionModel->findAll([], 'created_at DESC', $perPage, $offset);
        $totalTransactions = $this->transactionModel->count([]);

        $this->currentPage = 'admin-transactions';
        $this->render('admin/transactions', [
            'pageTitle' => 'Admin - Transactions',
            'currentPage' => $this->currentPage,
            'transactions' => $transactions,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => ceil($totalTransactions / $perPage),
                'total' => $totalTransactions,
                'per_page' => $perPage
            ]
        ], ['admin'], ['admin']);
    }

    /**
     * Show system settings
     */
    public function settings(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Load current settings from config
        $config = require CONFIG_PATH . '/app.php';

        $this->currentPage = 'admin-settings';
        $this->render('admin/settings', [
            'pageTitle' => 'Admin - Settings',
            'currentPage' => $this->currentPage,
            'config' => $config
        ], ['admin'], ['admin']);
    }

    /**
     * Update system settings
     */
    public function updateSettings(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        // Get settings from POST
        $settings = [
            'app_name' => trim($_POST['app_name'] ?? ''),
            'maintenance_mode' => isset($_POST['maintenance_mode']) ? 1 : 0,
            'registration_enabled' => isset($_POST['registration_enabled']) ? 1 : 0,
            'default_credits' => (float)($_POST['default_credits'] ?? 0),
            'rate_limit_enabled' => isset($_POST['rate_limit_enabled']) ? 1 : 0
        ];

        // Log the settings change
        $this->auditService->log($user['id'], 'settings_updated', $settings);

        // In a real app, this would save to database or config file
        $this->setFlash('success', 'Settings updated successfully');
        $this->redirect('/admin/settings');
    }
}
