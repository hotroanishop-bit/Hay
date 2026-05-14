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
    private Plan $planModel;
    private Provider $providerModel;
    private ModelPricing $modelPricingModel;
    private Deposit $depositModel;
    private AuditLog $auditLogModel;
    private UsageLog $usageLogModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        
        $this->transactionModel = new Transaction();
        $this->ticketModel = new Ticket();
        $this->apiKeyModel = new ApiKey();
        $this->auditService = new AuditService();
        $this->planModel = new Plan();
        $this->providerModel = new Provider();
        $this->modelPricingModel = new ModelPricing();
        $this->depositModel = new Deposit();
        $this->auditLogModel = new AuditLog();
        $this->usageLogModel = new UsageLog();
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

    // =====================
    // Dashboard
    // =====================

    /**
     * Admin Dashboard with stats and charts
     */
    public function dashboard(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Get total users count
        $totalUsers = $this->userModel->count([]);
        
        // Get active users today (users with usage_logs today)
        $today = date('Y-m-d');
        $sql = "SELECT COUNT(DISTINCT user_id) as active_count FROM usage_logs WHERE DATE(created_at) = :today";
        $activeResult = $this->usageLogModel->query($sql, ['today' => $today]);
        $activeUsersToday = (int) ($activeResult[0]['active_count'] ?? 0);
        
        // Get total revenue (sum of approved deposits)
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved'";
        $revenueResult = $this->depositModel->query($sql, []);
        $totalRevenue = (float) ($revenueResult[0]['total'] ?? 0);
        
        // Get total API calls today
        $sql = "SELECT COUNT(*) as total FROM usage_logs WHERE DATE(created_at) = :today";
        $apiResult = $this->usageLogModel->query($sql, ['today' => $today]);
        $apiCallsToday = (int) ($apiResult[0]['total'] ?? 0);
        
        // Get pending deposits count
        $sql = "SELECT COUNT(*) as total FROM deposits WHERE status = 'pending'";
        $pendingDepositsResult = $this->depositModel->query($sql, []);
        $pendingDeposits = (int) ($pendingDepositsResult[0]['total'] ?? 0);
        
        // Get pending tickets count (open tickets)
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE status = 'open'";
        $pendingTicketsResult = $this->ticketModel->query($sql, []);
        $pendingTickets = (int) ($pendingTicketsResult[0]['total'] ?? 0);
        
        // Get revenue last 7 days (for chart)
        $revenueLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved' AND DATE(processed_at) = :date";
            $result = $this->depositModel->query($sql, ['date' => $date]);
            $revenueLast7Days[] = [
                'date' => $date,
                'label' => date('M d', strtotime($date)),
                'amount' => (float) ($result[0]['total'] ?? 0)
            ];
        }
        
        // Get signups last 7 days (for chart)
        $signupsLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sql = "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = :date";
            $result = $this->userModel->query($sql, ['date' => $date]);
            $signupsLast7Days[] = [
                'date' => $date,
                'label' => date('M d', strtotime($date)),
                'count' => (int) ($result[0]['total'] ?? 0)
            ];
        }
        
        // Get recent activity (last 10 audit logs)
        $recentActivity = $this->auditLogModel->getRecent(10);

        $this->currentPage = 'admin-dashboard';
        $this->render('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'currentPage' => $this->currentPage,
            'totalUsers' => $totalUsers,
            'activeUsersToday' => $activeUsersToday,
            'totalRevenue' => $totalRevenue,
            'apiCallsToday' => $apiCallsToday,
            'pendingDeposits' => $pendingDeposits,
            'pendingTickets' => $pendingTickets,
            'revenueLast7Days' => $revenueLast7Days,
            'signupsLast7Days' => $signupsLast7Days,
            'recentActivity' => $recentActivity
        ], ['admin_dashboard'], ['admin_dashboard']);
    }

    // =====================
    // Deposit Management
    // =====================

    /**
     * List all deposits with filtering
     */
    public function deposits(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Build query with optional status filter
        $params = [];
        $whereClause = '';
        if ($status && in_array($status, ['pending', 'approved', 'rejected', 'expired'])) {
            $whereClause = 'WHERE d.status = :status';
            $params['status'] = $status;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM deposits d {$whereClause}";
        $countResult = $this->depositModel->query($countSql, $params);
        $total = (int) ($countResult[0]['total'] ?? 0);

        // Get deposits with user info
        $sql = "SELECT d.*, u.name as user_name, u.email as user_email 
                FROM deposits d 
                LEFT JOIN users u ON d.user_id = u.id 
                {$whereClause} 
                ORDER BY d.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $deposits = $this->depositModel->query($sql, $params);

        // Get counts by status for tabs
        $statusCounts = $this->getDepositStatusCounts();

        $this->currentPage = 'admin-deposits';
        $this->render('admin/deposits', [
            'pageTitle' => 'Admin - Deposits',
            'currentPage' => $this->currentPage,
            'deposits' => $deposits,
            'statusFilter' => $status,
            'statusCounts' => $statusCounts,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ], ['admin'], ['admin']);
    }

    /**
     * Get deposit counts by status
     */
    private function getDepositStatusCounts(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM deposits GROUP BY status";
        $results = $this->depositModel->query($sql, []);
        
        $counts = [
            'all' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'expired' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['all'] += (int) $row['count'];
        }
        
        return $counts;
    }

    /**
     * Show deposit detail
     */
    public function depositDetail(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Get deposit with user info
        $sql = "SELECT d.*, u.name as user_name, u.email as user_email, u.balance as user_balance 
                FROM deposits d 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE d.id = :id";
        $results = $this->depositModel->query($sql, ['id' => $id]);
        $deposit = $results[0] ?? null;

        if (!$deposit) {
            $this->setFlash('error', 'Deposit not found');
            $this->redirect('/admin/deposits');
            return;
        }

        // Get audit history for this deposit
        $auditHistory = $this->auditLogModel->getByTarget('deposit', $id);

        $this->currentPage = 'admin-deposits';
        $this->render('admin/deposit_detail', [
            'pageTitle' => 'Admin - Deposit Detail',
            'currentPage' => $this->currentPage,
            'deposit' => $deposit,
            'auditHistory' => $auditHistory
        ], ['admin'], ['admin']);
    }

    /**
     * Approve a deposit
     */
    public function approveDeposit(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $deposit = $this->depositModel->find($id);

        if (!$deposit) {
            $this->setFlash('error', 'Deposit not found');
            $this->redirect('/admin/deposits');
            return;
        }

        if ($deposit['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending deposits can be approved');
            $this->redirect('/admin/deposits/' . $id);
            return;
        }

        // Update deposit status
        $this->depositModel->updateStatus($id, 'approved', $admin['id']);

        // Add amount to user's balance
        $this->userModel->updateBalance($deposit['user_id'], $deposit['amount']);

        // Create transaction record
        $this->transactionModel->createCredit(
            $deposit['user_id'],
            $deposit['amount'],
            'Deposit approved - Ref: ' . $deposit['reference_code'],
            ['reference_id' => $deposit['reference_code'], 'payment_method' => 'bank_transfer']
        );

        // Log audit action
        $this->auditLogModel->logAction(
            $admin['id'],
            'deposit_approved',
            'deposit',
            $id,
            ['status' => 'pending'],
            ['status' => 'approved', 'amount' => $deposit['amount']],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Deposit approved. $' . number_format($deposit['amount'], 2) . ' added to user balance.');
        $this->redirect('/admin/deposits/' . $id);
    }

    /**
     * Reject a deposit
     */
    public function rejectDeposit(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $deposit = $this->depositModel->find($id);
        $reason = trim($_POST['reason'] ?? '');

        if (!$deposit) {
            $this->setFlash('error', 'Deposit not found');
            $this->redirect('/admin/deposits');
            return;
        }

        if ($deposit['status'] !== 'pending') {
            $this->setFlash('error', 'Only pending deposits can be rejected');
            $this->redirect('/admin/deposits/' . $id);
            return;
        }

        // Update deposit status
        $this->depositModel->updateStatus($id, 'rejected', $admin['id']);

        // Log audit action with reason
        $this->auditLogModel->logAction(
            $admin['id'],
            'deposit_rejected',
            'deposit',
            $id,
            ['status' => 'pending'],
            ['status' => 'rejected', 'reason' => $reason],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Deposit rejected.');
        $this->redirect('/admin/deposits/' . $id);
    }

    // =====================
    // Ticket Management
    // =====================

    /**
     * List all tickets with filtering
     */
    public function tickets(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $status = isset($_GET['status']) ? trim($_GET['status']) : null;
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        // Build query with optional status filter
        $params = [];
        $whereClause = '';
        if ($status && in_array($status, ['open', 'pending', 'closed'])) {
            $whereClause = 'WHERE t.status = :status';
            $params['status'] = $status;
        }

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM tickets t {$whereClause}";
        $countResult = $this->ticketModel->query($countSql, $params);
        $total = (int) ($countResult[0]['total'] ?? 0);

        // Get tickets with user info
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email 
                FROM tickets t 
                LEFT JOIN users u ON t.user_id = u.id 
                {$whereClause} 
                ORDER BY 
                    CASE t.status 
                        WHEN 'open' THEN 1 
                        WHEN 'pending' THEN 2 
                        WHEN 'closed' THEN 3 
                    END,
                    CASE t.priority 
                        WHEN 'high' THEN 1 
                        WHEN 'medium' THEN 2 
                        WHEN 'low' THEN 3 
                    END,
                    t.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $tickets = $this->ticketModel->query($sql, $params);

        // Get counts by status for tabs
        $statusCounts = $this->ticketModel->getAllStatusCounts();

        $this->currentPage = 'admin-tickets';
        $this->render('admin/tickets', [
            'pageTitle' => 'Admin - Tickets',
            'currentPage' => $this->currentPage,
            'tickets' => $tickets,
            'statusFilter' => $status,
            'statusCounts' => $statusCounts,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ], ['admin'], ['admin']);
    }

    /**
     * Show ticket detail
     */
    public function ticketDetail(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Get ticket with user info
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email, u.avatar_url as user_avatar 
                FROM tickets t 
                LEFT JOIN users u ON t.user_id = u.id 
                WHERE t.id = :id";
        $results = $this->ticketModel->query($sql, ['id' => $id]);
        $ticket = $results[0] ?? null;

        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/admin/tickets');
            return;
        }

        $this->currentPage = 'admin-tickets';
        $this->render('admin/ticket_detail', [
            'pageTitle' => 'Admin - Ticket #' . $id,
            'currentPage' => $this->currentPage,
            'ticket' => $ticket
        ], ['admin'], ['admin']);
    }

    /**
     * Reply to a ticket
     */
    public function replyTicket(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $ticket = $this->ticketModel->find($id);
        $reply = trim($_POST['reply'] ?? '');

        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/admin/tickets');
            return;
        }

        if (empty($reply)) {
            $this->setFlash('error', 'Reply cannot be empty');
            $this->redirect('/admin/tickets/' . $id);
            return;
        }

        // Add admin reply
        $this->ticketModel->addReply($id, $reply);

        // Log audit action
        $this->auditLogModel->logAction(
            $admin['id'],
            'ticket_replied',
            'ticket',
            $id,
            null,
            ['reply_length' => strlen($reply)],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Reply sent successfully.');
        $this->redirect('/admin/tickets/' . $id);
    }

    /**
     * Close a ticket
     */
    public function closeTicket(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $ticket = $this->ticketModel->find($id);

        if (!$ticket) {
            $this->setFlash('error', 'Ticket not found');
            $this->redirect('/admin/tickets');
            return;
        }

        // Close the ticket
        $this->ticketModel->close($id);

        // Log audit action
        $this->auditLogModel->logAction(
            $admin['id'],
            'ticket_closed',
            'ticket',
            $id,
            ['status' => $ticket['status']],
            ['status' => 'closed'],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Ticket closed.');
        $this->redirect('/admin/tickets/' . $id);
    }

    // =====================
    // User Management Enhancements
    // =====================

    /**
     * Ban a user
     */
    public function banUser(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $user = $this->userModel->find($id);
        $reason = trim($_POST['reason'] ?? '');

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/users');
            return;
        }

        if ($user['is_admin']) {
            $this->setFlash('error', 'Cannot ban an admin user');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        // Ban the user
        $this->userModel->ban($id);

        // Log audit action with reason
        $this->auditLogModel->logAction(
            $admin['id'],
            'user_banned',
            'user',
            $id,
            ['is_banned' => false],
            ['is_banned' => true, 'reason' => $reason],
            $this->getClientIP()
        );

        $this->setFlash('success', 'User has been banned.');
        $this->redirect('/admin/users/' . $id);
    }

    /**
     * Unban a user
     */
    public function unbanUser(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $user = $this->userModel->find($id);

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/users');
            return;
        }

        // Unban the user
        $this->userModel->unban($id);

        // Log audit action
        $this->auditLogModel->logAction(
            $admin['id'],
            'user_unbanned',
            'user',
            $id,
            ['is_banned' => true],
            ['is_banned' => false],
            $this->getClientIP()
        );

        $this->setFlash('success', 'User has been unbanned.');
        $this->redirect('/admin/users/' . $id);
    }

    /**
     * Update user balance manually
     */
    public function updateUserBalance(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $user = $this->userModel->find($id);
        $amount = (float) ($_POST['amount'] ?? 0);

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/users');
            return;
        }

        if ($amount == 0) {
            $this->setFlash('error', 'Amount cannot be zero');
            $this->redirect('/admin/users/' . $id);
            return;
        }

        $oldBalance = (float) $user['balance'];
        $newBalance = $oldBalance + $amount;

        // Update balance
        $this->userModel->updateBalance($id, $amount);

        // Create transaction record
        if ($amount > 0) {
            $this->transactionModel->createCredit(
                $id,
                $amount,
                'Manual balance adjustment by admin',
                ['payment_method' => 'admin_adjustment']
            );
        } else {
            $this->transactionModel->createDebit(
                $id,
                abs($amount),
                'Manual balance deduction by admin',
                ['payment_method' => 'admin_adjustment']
            );
        }

        // Log audit action
        $this->auditLogModel->logAction(
            $admin['id'],
            'user_balance_updated',
            'user',
            $id,
            ['balance' => $oldBalance],
            ['balance' => $newBalance, 'adjustment' => $amount],
            $this->getClientIP()
        );

        $this->setFlash('success', 'User balance updated. Old: $' . number_format($oldBalance, 2) . ' -> New: $' . number_format($newBalance, 2));
        $this->redirect('/admin/users/' . $id);
    }

    /**
     * Update user plan
     */
    public function updateUserPlan(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $user = $this->userModel->find($id);
        $planId = (int) ($_POST['plan_id'] ?? 0);

        if (!$user) {
            $this->setFlash('error', 'User not found');
            $this->redirect('/admin/users');
            return;
        }

        $plan = null;
        if ($planId > 0) {
            $plan = $this->planModel->find($planId);
            if (!$plan) {
                $this->setFlash('error', 'Plan not found');
                $this->redirect('/admin/users/' . $id);
                return;
            }
        }

        $oldPlanId = $user['plan_id'] ?? null;

        // Update user's plan
        $this->userModel->update($id, [
            'plan_id' => $planId > 0 ? $planId : null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log audit action
        $this->auditLogModel->logAction(
            $admin['id'],
            'user_plan_updated',
            'user',
            $id,
            ['plan_id' => $oldPlanId],
            ['plan_id' => $planId > 0 ? $planId : null, 'plan_name' => $plan ? $plan['name'] : 'None'],
            $this->getClientIP()
        );

        $planName = $plan ? $plan['name'] : 'None';
        $this->setFlash('success', "User's plan updated to: {$planName}");
        $this->redirect('/admin/users/' . $id);
    }

    // =====================
    // Audit Logs
    // =====================

    /**
     * Show audit logs
     */
    public function auditLogs(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 50;

        // Get filters
        $filters = [
            'admin_id' => isset($_GET['admin_id']) && $_GET['admin_id'] !== '' ? (int)$_GET['admin_id'] : null,
            'action' => isset($_GET['action']) && $_GET['action'] !== '' ? trim($_GET['action']) : null,
            'date_from' => isset($_GET['date_from']) && $_GET['date_from'] !== '' ? trim($_GET['date_from']) : null,
            'date_to' => isset($_GET['date_to']) && $_GET['date_to'] !== '' ? trim($_GET['date_to']) : null,
        ];

        // Search audit logs with filters
        $result = $this->auditLogModel->search($filters, $page, $perPage);

        // Get list of admins for filter dropdown
        $sql = "SELECT DISTINCT u.id, u.name FROM users u 
                INNER JOIN audit_logs al ON u.id = al.admin_id 
                ORDER BY u.name";
        $admins = $this->userModel->query($sql, []);

        // Get list of action types
        $sql = "SELECT DISTINCT action FROM audit_logs ORDER BY action";
        $actionTypes = $this->auditLogModel->query($sql, []);

        $this->currentPage = 'admin-logs';
        $this->render('admin/logs', [
            'pageTitle' => 'Admin - Audit Logs',
            'currentPage' => $this->currentPage,
            'logs' => $result['data'],
            'admins' => $admins,
            'actionTypes' => array_column($actionTypes, 'action'),
            'filters' => $filters,
            'pagination' => [
                'current_page' => $result['page'],
                'total_pages' => $result['total_pages'],
                'total' => $result['total'],
                'per_page' => $result['per_page']
            ]
        ], ['admin'], ['admin']);
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
        $search = isset($_GET['search']) ? trim($_GET['search']) : null;

        $result = $this->userModel->getAllUsers($page, $perPage, $search);
        $users = $result['data'] ?? [];
        $totalUsers = $result['total'] ?? 0;

        // Get ticket counts for admin overview
        $ticketCounts = $this->ticketModel->getAllStatusCounts();

        $this->currentPage = 'admin-users';
        $this->render('admin/users', [
            'pageTitle' => 'Admin - Users',
            'currentPage' => $this->currentPage,
            'users' => $users,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int) ceil($totalUsers / $perPage),
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

        // Load settings from SettingsService (database)
        $settingsService = new SettingsService();
        $settings = $settingsService->getAll();
        
        // Load plans for default_plan dropdown
        $plans = $this->planModel->findActive();
        
        // Load bank list for bank dropdown
        $bankList = $settingsService->getBankList();

        $this->currentPage = 'admin-settings';
        $this->render('admin/settings', [
            'pageTitle' => 'Admin - Settings',
            'currentPage' => $this->currentPage,
            'settings' => $settings,
            'plans' => $plans,
            'bankList' => $bankList
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

        $admin = $this->authService->user();
        $settingsService = new SettingsService();
        
        // Define settings configuration with validation types
        $settingsConfig = [
            // General Settings
            'site_name' => ['type' => 'string', 'required' => true],
            'site_url' => ['type' => 'string', 'required' => true, 'validate' => 'url'],
            'logo_url' => ['type' => 'string', 'required' => false, 'validate' => 'url_or_empty'],
            'favicon_url' => ['type' => 'string', 'required' => false, 'validate' => 'url_or_empty'],
            
            // Maintenance Settings
            'maintenance_mode' => ['type' => 'bool', 'required' => false],
            'maintenance_message' => ['type' => 'string', 'required' => false],
            
            // Payment Settings (VietQR)
            'bank_name' => ['type' => 'string', 'required' => true],
            'bank_account_number' => ['type' => 'string', 'required' => true],
            'account_holder_name' => ['type' => 'string', 'required' => true],
            
            // Email Settings (SMTP)
            'smtp_host' => ['type' => 'string', 'required' => false],
            'smtp_port' => ['type' => 'int', 'required' => false, 'min' => 1, 'max' => 65535],
            'smtp_username' => ['type' => 'string', 'required' => false],
            'smtp_password' => ['type' => 'string', 'required' => false, 'sensitive' => true],
            'smtp_encryption' => ['type' => 'string', 'required' => false],
            
            // Limits Settings
            'default_plan_id' => ['type' => 'int', 'required' => false],
            'min_deposit' => ['type' => 'int', 'required' => false, 'min' => 1000],
            'max_deposit' => ['type' => 'int', 'required' => false, 'min' => 1000],
        ];
        
        $errors = [];
        $changedSettings = [];
        
        foreach ($settingsConfig as $key => $config) {
            // Get the posted value
            if ($config['type'] === 'bool') {
                $newValue = isset($_POST[$key]) ? true : false;
            } else {
                $newValue = trim($_POST[$key] ?? '');
            }
            
            // Get current value for comparison
            $oldValue = $settingsService->get($key);
            
            // Handle SMTP password specially - skip if empty (keep existing)
            if ($key === 'smtp_password' && empty($newValue)) {
                continue;
            }
            
            // Validate required fields
            if (!empty($config['required']) && empty($newValue) && $newValue !== false && $newValue !== 0) {
                $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' is required.';
                continue;
            }
            
            // Validate URLs
            if (!empty($config['validate'])) {
                if ($config['validate'] === 'url' && !filter_var($newValue, FILTER_VALIDATE_URL)) {
                    $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be a valid URL.';
                    continue;
                }
                if ($config['validate'] === 'url_or_empty' && !empty($newValue) && !filter_var($newValue, FILTER_VALIDATE_URL)) {
                    $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be a valid URL or empty.';
                    continue;
                }
            }
            
            // Validate numeric ranges
            if ($config['type'] === 'int') {
                $newValue = (int) $newValue;
                if (isset($config['min']) && $newValue < $config['min']) {
                    $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be at least ' . number_format($config['min']) . '.';
                    continue;
                }
                if (isset($config['max']) && $newValue > $config['max']) {
                    $errors[] = ucfirst(str_replace('_', ' ', $key)) . ' must be at most ' . number_format($config['max']) . '.';
                    continue;
                }
            }
            
            // Cast type
            if ($config['type'] === 'int') {
                $newValue = (int) $newValue;
            } elseif ($config['type'] === 'bool') {
                $newValue = (bool) $newValue;
            }
            
            // Check if value has changed
            if ($oldValue !== $newValue) {
                $changedSettings[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'sensitive' => !empty($config['sensitive'])
                ];
            }
        }
        
        // Validate min_deposit < max_deposit
        $minDeposit = (int) ($_POST['min_deposit'] ?? 0);
        $maxDeposit = (int) ($_POST['max_deposit'] ?? 0);
        if ($minDeposit > 0 && $maxDeposit > 0 && $minDeposit > $maxDeposit) {
            $errors[] = 'Minimum deposit cannot be greater than maximum deposit.';
        }
        
        // If validation errors, redirect back with errors
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('/admin/settings');
            return;
        }
        
        // Save each changed setting
        foreach ($changedSettings as $key => $change) {
            $type = 'string';
            if (is_bool($change['new'])) {
                $type = 'bool';
            } elseif (is_int($change['new'])) {
                $type = 'int';
            }
            
            $settingsService->set($key, $change['new'], $type);
        }
        
        // Log audit action for all changed settings (excluding sensitive values)
        if (!empty($changedSettings)) {
            $oldValues = [];
            $newValues = [];
            
            foreach ($changedSettings as $key => $change) {
                if ($change['sensitive']) {
                    // Don't log sensitive values like passwords
                    $oldValues[$key] = '[REDACTED]';
                    $newValues[$key] = '[REDACTED]';
                } else {
                    $oldValues[$key] = $change['old'];
                    $newValues[$key] = $change['new'];
                }
            }
            
            $this->auditLogModel->logAction(
                $admin['id'],
                'settings_updated',
                'settings',
                null,
                $oldValues,
                $newValues,
                $this->getClientIP()
            );
        }

        $count = count($changedSettings);
        if ($count > 0) {
            $this->setFlash('success', $count . ' setting(s) updated successfully.');
        } else {
            $this->setFlash('info', 'No settings were changed.');
        }
        $this->redirect('/admin/settings');
    }

    // =====================
    // Plans Management
    // =====================

    /**
     * List all plans
     */
    public function plans(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $plans = $this->planModel->findAll([], 'price_monthly ASC');

        $this->currentPage = 'admin-plans';
        $this->render('admin/plans', [
            'pageTitle' => 'Admin - Plans',
            'currentPage' => $this->currentPage,
            'plans' => $plans
        ], ['admin'], ['admin']);
    }

    /**
     * Show create plan form
     */
    public function createPlan(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-plans';
        $this->render('admin/plan_form', [
            'pageTitle' => 'Admin - Create Plan',
            'currentPage' => $this->currentPage,
            'plan' => null,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new plan
     */
    public function storePlan(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'rate_limit_per_minute' => (int)($_POST['rate_limit_per_minute'] ?? 60),
            'daily_token_limit' => (int)($_POST['daily_token_limit'] ?? 100000),
            'price_multiplier' => (float)($_POST['price_multiplier'] ?? 1.0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['name'])) {
            $this->setFlash('error', 'Plan name is required');
            $this->redirect('/admin/plans/create');
            return;
        }

        $planId = $this->planModel->create($data);
        $this->auditService->log($user['id'], 'plan_created', ['plan_id' => $planId, 'name' => $data['name']]);

        $this->setFlash('success', 'Plan created successfully');
        $this->redirect('/admin/plans');
    }

    /**
     * Show edit plan form
     */
    public function editPlan(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $plan = $this->planModel->find($id);

        if (!$plan) {
            $this->setFlash('error', 'Plan not found');
            $this->redirect('/admin/plans');
            return;
        }

        $this->currentPage = 'admin-plans';
        $this->render('admin/plan_form', [
            'pageTitle' => 'Admin - Edit Plan',
            'currentPage' => $this->currentPage,
            'plan' => $plan,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a plan
     */
    public function updatePlan(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $plan = $this->planModel->find($id);

        if (!$plan) {
            $this->setFlash('error', 'Plan not found');
            $this->redirect('/admin/plans');
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'rate_limit_per_minute' => (int)($_POST['rate_limit_per_minute'] ?? 60),
            'daily_token_limit' => (int)($_POST['daily_token_limit'] ?? 100000),
            'price_multiplier' => (float)($_POST['price_multiplier'] ?? 1.0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['name'])) {
            $this->setFlash('error', 'Plan name is required');
            $this->redirect('/admin/plans/' . $id . '/edit');
            return;
        }

        $this->planModel->update($id, $data);
        $this->auditService->log($user['id'], 'plan_updated', ['plan_id' => $id, 'name' => $data['name']]);

        $this->setFlash('success', 'Plan updated successfully');
        $this->redirect('/admin/plans');
    }

    /**
     * Delete (deactivate) a plan
     */
    public function deletePlan(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $plan = $this->planModel->find($id);

        if (!$plan) {
            $this->setFlash('error', 'Plan not found');
            $this->redirect('/admin/plans');
            return;
        }

        // Check if plan has users
        $usersCount = $this->planModel->getUsersCount($id);
        if ($usersCount > 0) {
            $this->setFlash('error', 'Cannot delete plan with active users. Deactivate it instead.');
            $this->redirect('/admin/plans');
            return;
        }

        // Soft delete by deactivating
        $this->planModel->update($id, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
        $this->auditService->log($user['id'], 'plan_deleted', ['plan_id' => $id, 'name' => $plan['name']]);

        $this->setFlash('success', 'Plan deactivated successfully');
        $this->redirect('/admin/plans');
    }

    // =====================
    // Providers Management
    // =====================

    /**
     * List all providers
     */
    public function providers(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $providers = $this->providerModel->findAll([], 'name ASC');

        // Mask API keys for display
        foreach ($providers as &$provider) {
            $provider['api_key_masked'] = $this->maskApiKey($provider['api_key_encrypted'] ?? '');
        }

        $this->currentPage = 'admin-providers';
        $this->render('admin/providers', [
            'pageTitle' => 'Admin - Providers',
            'currentPage' => $this->currentPage,
            'providers' => $providers
        ], ['admin'], ['admin']);
    }

    /**
     * Mask an API key for display (show first 4 and last 4 chars)
     */
    private function maskApiKey(string $encryptedKey): string
    {
        if (empty($encryptedKey)) {
            return 'Not set';
        }

        // Decode to get actual key for masking display
        $decoded = base64_decode($encryptedKey, true);
        if ($decoded === false || strlen($decoded) < 8) {
            return '****';
        }

        return substr($decoded, 0, 4) . '****' . substr($decoded, -4);
    }

    /**
     * Show create provider form
     */
    public function createProvider(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-providers';
        $this->render('admin/provider_form', [
            'pageTitle' => 'Admin - Create Provider',
            'currentPage' => $this->currentPage,
            'provider' => null,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new provider
     */
    public function storeProvider(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        $name = trim($_POST['name'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');

        if (empty($name) || empty($baseUrl)) {
            $this->setFlash('error', 'Provider name and base URL are required');
            $this->redirect('/admin/providers/create');
            return;
        }

        $data = [
            'name' => $name,
            'base_url' => $baseUrl,
            'api_key_encrypted' => !empty($apiKey) ? base64_encode($apiKey) : '',
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $providerId = $this->providerModel->create($data);
        $this->auditService->log($user['id'], 'provider_created', ['provider_id' => $providerId, 'name' => $name]);

        $this->setFlash('success', 'Provider created successfully');
        $this->redirect('/admin/providers');
    }

    /**
     * Show edit provider form
     */
    public function editProvider(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $provider = $this->providerModel->find($id);

        if (!$provider) {
            $this->setFlash('error', 'Provider not found');
            $this->redirect('/admin/providers');
            return;
        }

        $this->currentPage = 'admin-providers';
        $this->render('admin/provider_form', [
            'pageTitle' => 'Admin - Edit Provider',
            'currentPage' => $this->currentPage,
            'provider' => $provider,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a provider
     */
    public function updateProvider(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $provider = $this->providerModel->find($id);

        if (!$provider) {
            $this->setFlash('error', 'Provider not found');
            $this->redirect('/admin/providers');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $baseUrl = trim($_POST['base_url'] ?? '');
        $apiKey = trim($_POST['api_key'] ?? '');

        if (empty($name) || empty($baseUrl)) {
            $this->setFlash('error', 'Provider name and base URL are required');
            $this->redirect('/admin/providers/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $name,
            'base_url' => $baseUrl,
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Only update API key if provided (allows keeping existing key)
        if (!empty($apiKey)) {
            $data['api_key_encrypted'] = base64_encode($apiKey);
        }

        $this->providerModel->update($id, $data);
        $this->auditService->log($user['id'], 'provider_updated', ['provider_id' => $id, 'name' => $name]);

        $this->setFlash('success', 'Provider updated successfully');
        $this->redirect('/admin/providers');
    }

    /**
     * Delete (deactivate) a provider
     */
    public function deleteProvider(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $provider = $this->providerModel->find($id);

        if (!$provider) {
            $this->setFlash('error', 'Provider not found');
            $this->redirect('/admin/providers');
            return;
        }

        // Soft delete by deactivating
        $this->providerModel->deactivate($id);
        $this->auditService->log($user['id'], 'provider_deleted', ['provider_id' => $id, 'name' => $provider['name']]);

        $this->setFlash('success', 'Provider deactivated successfully');
        $this->redirect('/admin/providers');
    }

    // =====================
    // Model Pricing Management
    // =====================

    /**
     * List all model pricing
     */
    public function modelPricing(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Get all pricing with provider info
        $sql = "SELECT mp.*, p.name as provider_name 
                FROM model_pricing mp 
                LEFT JOIN providers p ON p.id = mp.provider_id 
                ORDER BY p.name ASC, mp.model_name ASC";
        $pricing = $this->modelPricingModel->query($sql);

        $this->currentPage = 'admin-model-pricing';
        $this->render('admin/model_pricing', [
            'pageTitle' => 'Admin - Model Pricing',
            'currentPage' => $this->currentPage,
            'pricing' => $pricing
        ], ['admin'], ['admin']);
    }

    /**
     * Show create model pricing form
     */
    public function createModelPricing(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $providers = $this->providerModel->findAll([], 'name ASC');

        $this->currentPage = 'admin-model-pricing';
        $this->render('admin/model_pricing_form', [
            'pageTitle' => 'Admin - Create Model Pricing',
            'currentPage' => $this->currentPage,
            'modelPricing' => null,
            'providers' => $providers,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store new model pricing
     */
    public function storeModelPricing(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        $data = [
            'provider_id' => (int)($_POST['provider_id'] ?? 0),
            'model_name' => trim($_POST['model_name'] ?? ''),
            'input_price_per_1k' => (float)($_POST['input_price_per_1k'] ?? 0),
            'output_price_per_1k' => (float)($_POST['output_price_per_1k'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['model_name']) || $data['provider_id'] <= 0) {
            $this->setFlash('error', 'Model name and provider are required');
            $this->redirect('/admin/model-pricing/create');
            return;
        }

        $pricingId = $this->modelPricingModel->create($data);
        $this->auditService->log($user['id'], 'model_pricing_created', ['pricing_id' => $pricingId, 'model_name' => $data['model_name']]);

        $this->setFlash('success', 'Model pricing created successfully');
        $this->redirect('/admin/model-pricing');
    }

    /**
     * Show edit model pricing form
     */
    public function editModelPricing(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $modelPricing = $this->modelPricingModel->find($id);

        if (!$modelPricing) {
            $this->setFlash('error', 'Model pricing not found');
            $this->redirect('/admin/model-pricing');
            return;
        }

        $providers = $this->providerModel->findAll([], 'name ASC');

        $this->currentPage = 'admin-model-pricing';
        $this->render('admin/model_pricing_form', [
            'pageTitle' => 'Admin - Edit Model Pricing',
            'currentPage' => $this->currentPage,
            'modelPricing' => $modelPricing,
            'providers' => $providers,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update model pricing
     */
    public function updateModelPricing(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $modelPricing = $this->modelPricingModel->find($id);

        if (!$modelPricing) {
            $this->setFlash('error', 'Model pricing not found');
            $this->redirect('/admin/model-pricing');
            return;
        }

        $data = [
            'provider_id' => (int)($_POST['provider_id'] ?? 0),
            'model_name' => trim($_POST['model_name'] ?? ''),
            'input_price_per_1k' => (float)($_POST['input_price_per_1k'] ?? 0),
            'output_price_per_1k' => (float)($_POST['output_price_per_1k'] ?? 0),
            'is_active' => isset($_POST['is_active']) ? 1 : 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if (empty($data['model_name']) || $data['provider_id'] <= 0) {
            $this->setFlash('error', 'Model name and provider are required');
            $this->redirect('/admin/model-pricing/' . $id . '/edit');
            return;
        }

        $this->modelPricingModel->update($id, $data);
        $this->auditService->log($user['id'], 'model_pricing_updated', ['pricing_id' => $id, 'model_name' => $data['model_name']]);

        $this->setFlash('success', 'Model pricing updated successfully');
        $this->redirect('/admin/model-pricing');
    }

    /**
     * Delete (deactivate) model pricing
     */
    public function deleteModelPricing(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $modelPricing = $this->modelPricingModel->find($id);

        if (!$modelPricing) {
            $this->setFlash('error', 'Model pricing not found');
            $this->redirect('/admin/model-pricing');
            return;
        }

        // Soft delete by deactivating
        $this->modelPricingModel->deactivate($id);
        $this->auditService->log($user['id'], 'model_pricing_deleted', ['pricing_id' => $id, 'model_name' => $modelPricing['model_name']]);

        $this->setFlash('success', 'Model pricing deactivated successfully');
        $this->redirect('/admin/model-pricing');
    }
}
