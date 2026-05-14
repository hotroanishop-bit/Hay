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
    private Theme $themeModel;
    private CustomPage $customPageModel;
    private MenuItem $menuItemModel;
    private Notification $notificationModel;
    private Coupon $couponModel;
    private CouponUsage $couponUsageModel;
    private WebhookService $webhookService;
    private Changelog $changelogModel;

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
        $this->themeModel = new Theme();
        $this->customPageModel = new CustomPage();
        $this->menuItemModel = new MenuItem();
        $this->notificationModel = new Notification();
        $this->couponModel = new Coupon();
        $this->couponUsageModel = new CouponUsage();
        $this->webhookService = new WebhookService();
        $this->changelogModel = new Changelog();
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
     * Admin Dashboard with comprehensive stats and charts
     */
    public function dashboard(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $today = date('Y-m-d');
        $weekAgo = date('Y-m-d', strtotime('-7 days'));
        $monthAgo = date('Y-m-d', strtotime('-30 days'));

        // === USER STATISTICS ===
        
        // Get total users count
        $totalUsers = $this->userModel->count([]);
        
        // Get active users today (users with usage_logs today)
        $sql = "SELECT COUNT(DISTINCT user_id) as active_count FROM usage_logs WHERE DATE(created_at) = :today";
        $activeResult = $this->usageLogModel->query($sql, ['today' => $today]);
        $activeUsersToday = (int) ($activeResult[0]['active_count'] ?? 0);
        
        // Get new users this week
        $sql = "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) >= :week_ago";
        $newUsersResult = $this->userModel->query($sql, ['week_ago' => $weekAgo]);
        $newUsersThisWeek = (int) ($newUsersResult[0]['total'] ?? 0);
        
        // Get banned users count
        $sql = "SELECT COUNT(*) as total FROM users WHERE is_banned = 1";
        $bannedResult = $this->userModel->query($sql, []);
        $bannedUsers = (int) ($bannedResult[0]['total'] ?? 0);

        // === REVENUE STATISTICS ===
        
        // Get total revenue (all time - sum of approved deposits)
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved'";
        $revenueResult = $this->depositModel->query($sql, []);
        $totalRevenue = (float) ($revenueResult[0]['total'] ?? 0);
        
        // Get revenue this month
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved' AND DATE(processed_at) >= :month_ago";
        $monthRevenueResult = $this->depositModel->query($sql, ['month_ago' => $monthAgo]);
        $revenueThisMonth = (float) ($monthRevenueResult[0]['total'] ?? 0);
        
        // Get revenue today
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved' AND DATE(processed_at) = :today";
        $todayRevenueResult = $this->depositModel->query($sql, ['today' => $today]);
        $revenueToday = (float) ($todayRevenueResult[0]['total'] ?? 0);

        // === API STATISTICS ===
        
        // Get total API calls today
        $sql = "SELECT COUNT(*) as total FROM usage_logs WHERE DATE(created_at) = :today";
        $apiResult = $this->usageLogModel->query($sql, ['today' => $today]);
        $apiCallsToday = (int) ($apiResult[0]['total'] ?? 0);
        
        // Get total API calls this week
        $sql = "SELECT COUNT(*) as total FROM usage_logs WHERE DATE(created_at) >= :week_ago";
        $apiWeekResult = $this->usageLogModel->query($sql, ['week_ago' => $weekAgo]);
        $apiCallsThisWeek = (int) ($apiWeekResult[0]['total'] ?? 0);
        
        // Get tokens used today
        $sql = "SELECT COALESCE(SUM(tokens_used), 0) as total FROM usage_logs WHERE DATE(created_at) = :today";
        $tokensResult = $this->usageLogModel->query($sql, ['today' => $today]);
        $tokensUsedToday = (int) ($tokensResult[0]['total'] ?? 0);

        // === PENDING ITEMS ===
        
        // Get pending deposits count
        $sql = "SELECT COUNT(*) as total FROM deposits WHERE status = 'pending'";
        $pendingDepositsResult = $this->depositModel->query($sql, []);
        $pendingDeposits = (int) ($pendingDepositsResult[0]['total'] ?? 0);
        
        // Get pending tickets count (open tickets)
        $sql = "SELECT COUNT(*) as total FROM tickets WHERE status = 'open'";
        $pendingTicketsResult = $this->ticketModel->query($sql, []);
        $pendingTickets = (int) ($pendingTicketsResult[0]['total'] ?? 0);

        // === TOP USERS BY TOKEN USAGE (this month) ===
        $sql = "SELECT u.id, u.name, u.email, u.last_login_at,
                       COALESCE(SUM(ul.tokens_used), 0) as tokens_used,
                       COALESCE(SUM(ul.cost), 0) as cost
                FROM users u
                LEFT JOIN usage_logs ul ON u.id = ul.user_id AND DATE(ul.created_at) >= :month_ago
                GROUP BY u.id, u.name, u.email, u.last_login_at
                HAVING tokens_used > 0
                ORDER BY tokens_used DESC
                LIMIT 10";
        $topUsers = $this->userModel->query($sql, ['month_ago' => $monthAgo]);

        // === MODEL USAGE BREAKDOWN ===
        $sql = "SELECT model, COUNT(*) as call_count, COALESCE(SUM(tokens_used), 0) as total_tokens
                FROM usage_logs
                WHERE DATE(created_at) >= :month_ago
                GROUP BY model
                ORDER BY call_count DESC
                LIMIT 8";
        $modelUsage = $this->usageLogModel->query($sql, ['month_ago' => $monthAgo]);

        // === CHARTS DATA ===
        
        // Revenue last 30 days (for line chart)
        $revenueLast30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM deposits WHERE status = 'approved' AND DATE(processed_at) = :date";
            $result = $this->depositModel->query($sql, ['date' => $date]);
            $revenueLast30Days[] = [
                'date' => $date,
                'label' => date('M d', strtotime($date)),
                'value' => (float) ($result[0]['total'] ?? 0)
            ];
        }
        
        // Signups last 30 days (for bar chart)
        $signupsLast30Days = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sql = "SELECT COUNT(*) as total FROM users WHERE DATE(created_at) = :date";
            $result = $this->userModel->query($sql, ['date' => $date]);
            $signupsLast30Days[] = [
                'date' => $date,
                'label' => date('M d', strtotime($date)),
                'value' => (int) ($result[0]['total'] ?? 0)
            ];
        }
        
        // API calls last 7 days (for bar chart)
        $apiCallsLast7Days = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $sql = "SELECT COUNT(*) as total FROM usage_logs WHERE DATE(created_at) = :date";
            $result = $this->usageLogModel->query($sql, ['date' => $date]);
            $apiCallsLast7Days[] = [
                'date' => $date,
                'label' => date('D', strtotime($date)),
                'value' => (int) ($result[0]['total'] ?? 0)
            ];
        }

        // === RECENT ACTIVITY (last 10 audit logs) ===
        $recentActivity = $this->auditLogModel->getRecent(10);

        $this->currentPage = 'admin-dashboard';
        $this->render('admin/dashboard', [
            'pageTitle' => 'Admin Dashboard',
            'currentPage' => $this->currentPage,
            'adminName' => $admin['name'] ?? 'Admin',
            // User stats
            'totalUsers' => $totalUsers,
            'activeUsersToday' => $activeUsersToday,
            'newUsersThisWeek' => $newUsersThisWeek,
            'bannedUsers' => $bannedUsers,
            // Revenue stats
            'totalRevenue' => $totalRevenue,
            'revenueThisMonth' => $revenueThisMonth,
            'revenueToday' => $revenueToday,
            // API stats
            'apiCallsToday' => $apiCallsToday,
            'apiCallsThisWeek' => $apiCallsThisWeek,
            'tokensUsedToday' => $tokensUsedToday,
            // Pending items
            'pendingDeposits' => $pendingDeposits,
            'pendingTickets' => $pendingTickets,
            // Top users
            'topUsers' => $topUsers,
            // Model usage
            'modelUsage' => $modelUsage,
            // Chart data
            'revenueLast30Days' => $revenueLast30Days,
            'signupsLast30Days' => $signupsLast30Days,
            'apiCallsLast7Days' => $apiCallsLast7Days,
            // Recent activity
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

        // Wrap all operations in a database transaction to prevent partial state on failure
        try {
            $this->depositModel->beginTransaction();

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

            $this->depositModel->commit();

            // Trigger webhook notification (non-blocking, don't fail the main operation)
            try {
                $this->webhookService->triggerDepositApproved(
                    $deposit['user_id'],
                    $id,
                    (float) $deposit['amount']
                );
            } catch (Exception $webhookException) {
                // Log webhook failure but don't affect deposit approval
                error_log('Webhook trigger failed for deposit approval: ' . $webhookException->getMessage());
            }

            $this->setFlash('success', 'Deposit approved. $' . number_format($deposit['amount'], 2) . ' added to user balance.');
        } catch (Exception $e) {
            $this->depositModel->rollback();
            $this->setFlash('error', 'Failed to approve deposit. Please try again.');
        }

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

        // Trigger webhook notification (non-blocking)
        try {
            $this->webhookService->triggerDepositRejected(
                $deposit['user_id'],
                $id,
                (float) $deposit['amount'],
                $reason
            );
        } catch (Exception $webhookException) {
            // Log webhook failure but don't affect deposit rejection
            error_log('Webhook trigger failed for deposit rejection: ' . $webhookException->getMessage());
        }

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
        
        // Include TokenHelper if not already loaded
        $helperPath = dirname(__DIR__) . '/Helpers/TokenHelper.php';
        if (file_exists($helperPath) && !function_exists('parseTokenNotation')) {
            require_once $helperPath;
        }
        
        // Parse token_quota notation (e.g., 10k, 100k, 1M)
        $tokenQuotaInput = trim($_POST['token_quota'] ?? '0');
        $tokenQuota = function_exists('parseTokenNotation') 
            ? parseTokenNotation($tokenQuotaInput) 
            : (int) $tokenQuotaInput;
        
        $isFree = isset($_POST['is_free']) ? 1 : 0;

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'rate_limit_per_minute' => (int)($_POST['rate_limit_per_minute'] ?? 60),
            'daily_token_limit' => (int)($_POST['daily_token_limit'] ?? 100000),
            'price_multiplier' => (float)($_POST['price_multiplier'] ?? 1.0),
            'token_quota' => $tokenQuota,
            'duration_days' => (int)($_POST['duration_days'] ?? 30),
            'is_free' => $isFree,
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
        
        // Include TokenHelper if not already loaded
        $helperPath = dirname(__DIR__) . '/Helpers/TokenHelper.php';
        if (file_exists($helperPath) && !function_exists('parseTokenNotation')) {
            require_once $helperPath;
        }
        
        // Parse token_quota notation (e.g., 10k, 100k, 1M)
        $tokenQuotaInput = trim($_POST['token_quota'] ?? '0');
        $tokenQuota = function_exists('parseTokenNotation') 
            ? parseTokenNotation($tokenQuotaInput) 
            : (int) $tokenQuotaInput;
        
        $isFree = isset($_POST['is_free']) ? 1 : 0;

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'price_monthly' => (float)($_POST['price_monthly'] ?? 0),
            'rate_limit_per_minute' => (int)($_POST['rate_limit_per_minute'] ?? 60),
            'daily_token_limit' => (int)($_POST['daily_token_limit'] ?? 100000),
            'price_multiplier' => (float)($_POST['price_multiplier'] ?? 1.0),
            'token_quota' => $tokenQuota,
            'duration_days' => (int)($_POST['duration_days'] ?? 30),
            'is_free' => $isFree,
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

    // =====================
    // Themes Management
    // =====================

    /**
     * List all themes
     */
    public function themes(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $themes = $this->themeModel->findAll([], 'name ASC');

        $this->currentPage = 'admin-themes';
        $this->render('admin/themes', [
            'pageTitle' => 'Admin - Themes',
            'currentPage' => $this->currentPage,
            'themes' => $themes
        ], ['admin'], ['admin']);
    }

    /**
     * Show create theme form
     */
    public function createTheme(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-themes';
        $this->render('admin/theme_form', [
            'pageTitle' => 'Admin - Create Theme',
            'currentPage' => $this->currentPage,
            'theme' => null,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new theme
     */
    public function storeTheme(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $name = trim($_POST['name'] ?? '');
        $cssVariablesRaw = trim($_POST['css_variables'] ?? '{}');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Theme name is required');
            $this->redirect('/admin/themes/create');
            return;
        }

        // Validate JSON
        $cssVariables = json_decode($cssVariablesRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setFlash('error', 'CSS Variables must be valid JSON');
            $this->redirect('/admin/themes/create');
            return;
        }

        $data = [
            'name' => $name,
            'css_variables' => $cssVariablesRaw,
            'is_default' => $isDefault,
            'created_by' => $user['id'],
            'created_at' => date('Y-m-d H:i:s')
        ];

        $themeId = $this->themeModel->create($data);

        // If set as default, update others
        if ($isDefault) {
            $this->themeModel->setDefault($themeId);
        }

        $this->auditService->log($user['id'], 'theme_created', ['theme_id' => $themeId, 'name' => $name]);

        $this->setFlash('success', 'Theme created successfully');
        $this->redirect('/admin/themes');
    }

    /**
     * Show edit theme form
     */
    public function editTheme(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $theme = $this->themeModel->find($id);

        if (!$theme) {
            $this->setFlash('error', 'Theme not found');
            $this->redirect('/admin/themes');
            return;
        }

        $this->currentPage = 'admin-themes';
        $this->render('admin/theme_form', [
            'pageTitle' => 'Admin - Edit Theme',
            'currentPage' => $this->currentPage,
            'theme' => $theme,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a theme
     */
    public function updateTheme(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $theme = $this->themeModel->find($id);

        if (!$theme) {
            $this->setFlash('error', 'Theme not found');
            $this->redirect('/admin/themes');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $cssVariablesRaw = trim($_POST['css_variables'] ?? '{}');
        $isDefault = isset($_POST['is_default']) ? 1 : 0;

        if (empty($name)) {
            $this->setFlash('error', 'Theme name is required');
            $this->redirect('/admin/themes/' . $id . '/edit');
            return;
        }

        // Validate JSON
        $cssVariables = json_decode($cssVariablesRaw, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->setFlash('error', 'CSS Variables must be valid JSON');
            $this->redirect('/admin/themes/' . $id . '/edit');
            return;
        }

        $data = [
            'name' => $name,
            'css_variables' => $cssVariablesRaw,
            'is_default' => $isDefault
        ];

        $this->themeModel->update($id, $data);

        // If set as default, update others
        if ($isDefault) {
            $this->themeModel->setDefault($id);
        }

        $this->auditService->log($user['id'], 'theme_updated', ['theme_id' => $id, 'name' => $name]);

        $this->setFlash('success', 'Theme updated successfully');
        $this->redirect('/admin/themes');
    }

    /**
     * Delete a theme
     */
    public function deleteTheme(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $theme = $this->themeModel->find($id);

        if (!$theme) {
            $this->setFlash('error', 'Theme not found');
            $this->redirect('/admin/themes');
            return;
        }

        // Cannot delete default theme
        if (!empty($theme['is_default'])) {
            $this->setFlash('error', 'Cannot delete the default theme. Set another theme as default first.');
            $this->redirect('/admin/themes');
            return;
        }

        $this->themeModel->delete($id);
        $this->auditService->log($user['id'], 'theme_deleted', ['theme_id' => $id, 'name' => $theme['name']]);

        $this->setFlash('success', 'Theme deleted successfully');
        $this->redirect('/admin/themes');
    }

    /**
     * Set a theme as the default
     */
    public function setDefaultTheme(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $theme = $this->themeModel->find($id);

        if (!$theme) {
            $this->setFlash('error', 'Theme not found');
            $this->redirect('/admin/themes');
            return;
        }

        $this->themeModel->setDefault($id);
        $this->auditService->log($user['id'], 'theme_set_default', ['theme_id' => $id, 'name' => $theme['name']]);

        $this->setFlash('success', 'Theme "' . htmlspecialchars($theme['name']) . '" set as default');
        $this->redirect('/admin/themes');
    }

    // =====================
    // Custom Pages Management
    // =====================

    /**
     * List all custom pages
     */
    public function pages(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $pages = $this->customPageModel->findAll([], 'menu_order ASC, title ASC');

        $this->currentPage = 'admin-pages';
        $this->render('admin/pages', [
            'pageTitle' => 'Admin - Custom Pages',
            'currentPage' => $this->currentPage,
            'pages' => $pages
        ], ['admin'], ['admin']);
    }

    /**
     * Show create page form
     */
    public function createPage(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-pages';
        $this->render('admin/page_form', [
            'pageTitle' => 'Admin - Create Page',
            'currentPage' => $this->currentPage,
            'page' => null,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new custom page
     */
    public function storePage(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $menuOrder = (int)($_POST['menu_order'] ?? 0);
        $showInMenu = isset($_POST['show_in_menu']) ? 1 : 0;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if (empty($title)) {
            $this->setFlash('error', 'Page title is required');
            $this->redirect('/admin/pages/create');
            return;
        }

        // Auto-generate slug if empty
        if (empty($slug)) {
            $slug = $this->generateSlug($title);
        } else {
            $slug = $this->generateSlug($slug);
        }

        // Check for duplicate slug
        $existing = $this->customPageModel->findBySlug($slug);
        if ($existing) {
            $slug = $slug . '-' . time();
        }

        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'meta_description' => $metaDescription,
            'menu_order' => $menuOrder,
            'show_in_menu' => $showInMenu,
            'is_published' => $isPublished,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $pageId = $this->customPageModel->create($data);
        $this->auditService->log($user['id'], 'page_created', ['page_id' => $pageId, 'title' => $title, 'slug' => $slug]);

        $this->setFlash('success', 'Page created successfully');
        $this->redirect('/admin/pages');
    }

    /**
     * Show edit page form
     */
    public function editPage(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $page = $this->customPageModel->find($id);

        if (!$page) {
            $this->setFlash('error', 'Page not found');
            $this->redirect('/admin/pages');
            return;
        }

        $this->currentPage = 'admin-pages';
        $this->render('admin/page_form', [
            'pageTitle' => 'Admin - Edit Page',
            'currentPage' => $this->currentPage,
            'page' => $page,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a custom page
     */
    public function updatePage(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $page = $this->customPageModel->find($id);

        if (!$page) {
            $this->setFlash('error', 'Page not found');
            $this->redirect('/admin/pages');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $slug = trim($_POST['slug'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $metaDescription = trim($_POST['meta_description'] ?? '');
        $menuOrder = (int)($_POST['menu_order'] ?? 0);
        $showInMenu = isset($_POST['show_in_menu']) ? 1 : 0;
        $isPublished = isset($_POST['is_published']) ? 1 : 0;

        if (empty($title)) {
            $this->setFlash('error', 'Page title is required');
            $this->redirect('/admin/pages/' . $id . '/edit');
            return;
        }

        // Auto-generate slug if empty
        if (empty($slug)) {
            $slug = $this->generateSlug($title);
        } else {
            $slug = $this->generateSlug($slug);
        }

        // Check for duplicate slug (excluding current page)
        $sql = "SELECT id FROM custom_pages WHERE slug = :slug AND id != :id LIMIT 1";
        $existing = $this->customPageModel->query($sql, ['slug' => $slug, 'id' => $id]);
        if (!empty($existing)) {
            $slug = $slug . '-' . time();
        }

        $data = [
            'title' => $title,
            'slug' => $slug,
            'content' => $content,
            'meta_description' => $metaDescription,
            'menu_order' => $menuOrder,
            'show_in_menu' => $showInMenu,
            'is_published' => $isPublished,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->customPageModel->update($id, $data);
        $this->auditService->log($user['id'], 'page_updated', ['page_id' => $id, 'title' => $title]);

        $this->setFlash('success', 'Page updated successfully');
        $this->redirect('/admin/pages');
    }

    /**
     * Delete a custom page
     */
    public function deletePage(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $page = $this->customPageModel->find($id);

        if (!$page) {
            $this->setFlash('error', 'Page not found');
            $this->redirect('/admin/pages');
            return;
        }

        $this->customPageModel->delete($id);
        $this->auditService->log($user['id'], 'page_deleted', ['page_id' => $id, 'title' => $page['title']]);

        $this->setFlash('success', 'Page deleted successfully');
        $this->redirect('/admin/pages');
    }

    /**
     * Toggle page publish status
     */
    public function togglePublishPage(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $page = $this->customPageModel->find($id);

        if (!$page) {
            $this->setFlash('error', 'Page not found');
            $this->redirect('/admin/pages');
            return;
        }

        $newStatus = empty($page['is_published']) ? 1 : 0;
        $this->customPageModel->update($id, [
            'is_published' => $newStatus,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        $statusText = $newStatus ? 'published' : 'unpublished';
        $this->auditService->log($user['id'], 'page_' . $statusText, ['page_id' => $id, 'title' => $page['title']]);

        $this->setFlash('success', 'Page ' . $statusText . ' successfully');
        $this->redirect('/admin/pages');
    }

    /**
     * Generate URL slug from text
     */
    private function generateSlug(string $text): string
    {
        $slug = strtolower($text);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s_]+/', '-', $slug);
        $slug = preg_replace('/-+/', '-', $slug);
        $slug = trim($slug, '-');
        
        return $slug ?: 'page-' . time();
    }

    // =====================
    // Menu Items Management
    // =====================

    /**
     * List all menu items
     */
    public function menuItems(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $menuItems = $this->menuItemModel->findAll([], 'sort_order ASC, label ASC');

        $this->currentPage = 'admin-menu';
        $this->render('admin/menu', [
            'pageTitle' => 'Admin - Menu Items',
            'currentPage' => $this->currentPage,
            'menuItems' => $menuItems
        ], ['admin'], ['admin']);
    }

    /**
     * Show create menu item form
     */
    public function createMenuItem(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-menu';
        $this->render('admin/menu_form', [
            'pageTitle' => 'Admin - Create Menu Item',
            'currentPage' => $this->currentPage,
            'menuItem' => null,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new menu item
     */
    public function storeMenuItem(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        $label = trim($_POST['label'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $url = trim($_POST['url'] ?? '#');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $showInBottomNav = isset($_POST['show_in_bottom_nav']) ? 1 : 0;
        $showInBottomSheet = isset($_POST['show_in_bottom_sheet']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($label)) {
            $this->setFlash('error', 'Menu item label is required');
            $this->redirect('/admin/menu/create');
            return;
        }

        $data = [
            'label' => $label,
            'icon' => $icon,
            'url' => $url,
            'sort_order' => $sortOrder,
            'show_in_bottom_nav' => $showInBottomNav,
            'show_in_bottom_sheet' => $showInBottomSheet,
            'is_active' => $isActive,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $menuItemId = $this->menuItemModel->create($data);
        $this->auditService->log($user['id'], 'menu_item_created', ['menu_item_id' => $menuItemId, 'label' => $label]);

        $this->setFlash('success', 'Menu item created successfully');
        $this->redirect('/admin/menu');
    }

    /**
     * Show edit menu item form
     */
    public function editMenuItem(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $menuItem = $this->menuItemModel->find($id);

        if (!$menuItem) {
            $this->setFlash('error', 'Menu item not found');
            $this->redirect('/admin/menu');
            return;
        }

        $this->currentPage = 'admin-menu';
        $this->render('admin/menu_form', [
            'pageTitle' => 'Admin - Edit Menu Item',
            'currentPage' => $this->currentPage,
            'menuItem' => $menuItem,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a menu item
     */
    public function updateMenuItem(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $menuItem = $this->menuItemModel->find($id);

        if (!$menuItem) {
            $this->setFlash('error', 'Menu item not found');
            $this->redirect('/admin/menu');
            return;
        }

        $label = trim($_POST['label'] ?? '');
        $icon = trim($_POST['icon'] ?? '');
        $url = trim($_POST['url'] ?? '#');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $showInBottomNav = isset($_POST['show_in_bottom_nav']) ? 1 : 0;
        $showInBottomSheet = isset($_POST['show_in_bottom_sheet']) ? 1 : 0;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($label)) {
            $this->setFlash('error', 'Menu item label is required');
            $this->redirect('/admin/menu/' . $id . '/edit');
            return;
        }

        $data = [
            'label' => $label,
            'icon' => $icon,
            'url' => $url,
            'sort_order' => $sortOrder,
            'show_in_bottom_nav' => $showInBottomNav,
            'show_in_bottom_sheet' => $showInBottomSheet,
            'is_active' => $isActive,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $this->menuItemModel->update($id, $data);
        $this->auditService->log($user['id'], 'menu_item_updated', ['menu_item_id' => $id, 'label' => $label]);

        $this->setFlash('success', 'Menu item updated successfully');
        $this->redirect('/admin/menu');
    }

    /**
     * Delete a menu item
     */
    public function deleteMenuItem(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();
        $menuItem = $this->menuItemModel->find($id);

        if (!$menuItem) {
            $this->setFlash('error', 'Menu item not found');
            $this->redirect('/admin/menu');
            return;
        }

        $this->menuItemModel->delete($id);
        $this->auditService->log($user['id'], 'menu_item_deleted', ['menu_item_id' => $id, 'label' => $menuItem['label']]);

        $this->setFlash('success', 'Menu item deleted successfully');
        $this->redirect('/admin/menu');
    }

    /**
     * Reorder menu items via JSON POST
     */
    public function reorderMenuItems(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $user = $this->authService->user();

        // Get JSON body
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!is_array($data) || empty($data['items'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid data']);
            return;
        }

        try {
            $this->menuItemModel->reorder($data['items']);
            $this->auditService->log($user['id'], 'menu_items_reordered', ['count' => count($data['items'])]);

            echo json_encode(['success' => true]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Failed to reorder items']);
        }
    }

    // =====================
    // Admin Notifications Management
    // =====================

    /**
     * List all notifications (admin view)
     */
    public function adminNotifications(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 30;
        $offset = ($page - 1) * $perPage;

        // Get all notifications with user info
        $sql = "SELECT n.*, u.name as user_name, u.email as user_email 
                FROM notifications n 
                LEFT JOIN users u ON n.user_id = u.id 
                ORDER BY n.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $notifications = $this->notificationModel->query($sql);

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM notifications";
        $countResult = $this->notificationModel->query($countSql);
        $total = (int)($countResult[0]['total'] ?? 0);

        $this->currentPage = 'admin-notifications';
        $this->render('admin/notifications', [
            'pageTitle' => 'Admin - Notifications',
            'currentPage' => $this->currentPage,
            'notifications' => $notifications,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => (int)ceil($total / $perPage),
                'total' => $total,
                'per_page' => $perPage
            ]
        ], ['admin'], ['admin']);
    }

    /**
     * Show send notification form
     */
    public function sendNotification(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Get all users for dropdown
        $users = $this->userModel->findAll([], 'name ASC');

        $this->currentPage = 'admin-notifications';
        $this->render('admin/notification_form', [
            'pageTitle' => 'Admin - Send Notification',
            'currentPage' => $this->currentPage,
            'users' => $users
        ], ['admin'], ['admin']);
    }

    /**
     * Store (send) a new notification
     */
    public function storeNotification(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();

        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $type = trim($_POST['type'] ?? 'info');
        $isBroadcast = isset($_POST['is_broadcast']) ? true : false;
        $userId = (int)($_POST['user_id'] ?? 0);

        if (empty($title) || empty($message)) {
            $this->setFlash('error', 'Title and message are required');
            $this->redirect('/admin/notifications/send');
            return;
        }

        // Validate type
        $validTypes = ['info', 'success', 'warning', 'error'];
        if (!in_array($type, $validTypes)) {
            $type = 'info';
        }

        if ($isBroadcast) {
            // Create broadcast notification (null user_id)
            $notificationId = $this->notificationModel->createBroadcast($title, $message, $type);
            $this->auditService->log($admin['id'], 'notification_broadcast', [
                'notification_id' => $notificationId,
                'title' => $title,
                'type' => $type
            ]);
            $this->setFlash('success', 'Broadcast notification sent to all users');
        } else {
            if ($userId <= 0) {
                $this->setFlash('error', 'Please select a user or enable broadcast');
                $this->redirect('/admin/notifications/send');
                return;
            }

            // Verify user exists
            $user = $this->userModel->find($userId);
            if (!$user) {
                $this->setFlash('error', 'Selected user not found');
                $this->redirect('/admin/notifications/send');
                return;
            }

            // Create notification for specific user
            $notificationId = $this->notificationModel->create([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message,
                'type' => $type,
                'is_read' => 0,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            $this->auditService->log($admin['id'], 'notification_sent', [
                'notification_id' => $notificationId,
                'user_id' => $userId,
                'title' => $title,
                'type' => $type
            ]);
            $this->setFlash('success', 'Notification sent to ' . htmlspecialchars($user['name']));
        }

        $this->redirect('/admin/notifications');
    }

    /**
     * Delete a notification
     */
    public function deleteAdminNotification(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $notification = $this->notificationModel->find($id);

        if (!$notification) {
            $this->setFlash('error', 'Notification not found');
            $this->redirect('/admin/notifications');
            return;
        }

        $this->notificationModel->delete($id);
        $this->auditService->log($admin['id'], 'notification_deleted', [
            'notification_id' => $id,
            'title' => $notification['title']
        ]);

        $this->setFlash('success', 'Notification deleted successfully');
        $this->redirect('/admin/notifications');
    }

    // =====================
    // Coupon Management
    // =====================

    /**
     * List all coupons with stats
     */
    public function coupons(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $coupons = $this->couponModel->getAllWithStats();

        // Get summary stats
        $totalCoupons = count($coupons);
        $activeCoupons = count(array_filter($coupons, function($c) {
            return !empty($c['is_active']);
        }));
        $totalUsages = array_sum(array_column($coupons, 'total_usages'));
        $totalSavings = array_sum(array_column($coupons, 'total_savings'));

        $this->currentPage = 'admin-coupons';
        $this->render('admin/coupons', [
            'pageTitle' => 'Admin - Coupons',
            'currentPage' => $this->currentPage,
            'coupons' => $coupons,
            'stats' => [
                'total' => $totalCoupons,
                'active' => $activeCoupons,
                'usages' => $totalUsages,
                'savings' => $totalSavings
            ]
        ], ['admin'], ['admin']);
    }

    /**
     * Show create coupon form
     */
    public function createCoupon(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-coupons';
        $this->render('admin/coupon_form', [
            'pageTitle' => 'Admin - Create Coupon',
            'currentPage' => $this->currentPage,
            'coupon' => null,
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new coupon
     */
    public function storeCoupon(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();

        // Get form data
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = trim($_POST['type'] ?? 'percentage');
        $value = (float)($_POST['value'] ?? 0);
        $minAmount = (float)($_POST['min_amount'] ?? 0);
        $maxUses = (int)($_POST['max_uses'] ?? 0);
        $maxUsesPerUser = (int)($_POST['max_uses_per_user'] ?? 1);
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validate required fields
        if (empty($code)) {
            $this->setFlash('error', 'Coupon code is required');
            $this->redirect('/admin/coupons/create');
            return;
        }

        if ($value <= 0) {
            $this->setFlash('error', 'Coupon value must be greater than 0');
            $this->redirect('/admin/coupons/create');
            return;
        }

        // Validate code uniqueness
        if ($this->couponModel->codeExists($code)) {
            $this->setFlash('error', 'Coupon code already exists');
            $this->redirect('/admin/coupons/create');
            return;
        }

        // Validate type
        $validTypes = ['percentage', 'fixed', 'bonus'];
        if (!in_array($type, $validTypes)) {
            $type = 'percentage';
        }

        // Validate percentage value
        if ($type === 'percentage' && $value > 100) {
            $this->setFlash('error', 'Percentage discount cannot exceed 100%');
            $this->redirect('/admin/coupons/create');
            return;
        }

        // Create coupon
        $couponId = $this->couponModel->createCoupon([
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'min_amount' => $minAmount,
            'max_uses' => $maxUses > 0 ? $maxUses : null,
            'max_uses_per_user' => $maxUsesPerUser,
            'expires_at' => !empty($expiresAt) ? $expiresAt : null,
            'description' => $description,
            'is_active' => $isActive,
            'used_count' => 0
        ]);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            'coupon_created',
            'coupon',
            $couponId,
            null,
            ['code' => $code, 'type' => $type, 'value' => $value],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Coupon created successfully');
        $this->redirect('/admin/coupons');
    }

    /**
     * Show edit coupon form
     */
    public function editCoupon(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $coupon = $this->couponModel->find($id);

        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        $this->currentPage = 'admin-coupons';
        $this->render('admin/coupon_form', [
            'pageTitle' => 'Admin - Edit Coupon',
            'currentPage' => $this->currentPage,
            'coupon' => $coupon,
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a coupon
     */
    public function updateCoupon(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $coupon = $this->couponModel->find($id);

        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        // Get form data
        $code = strtoupper(trim($_POST['code'] ?? ''));
        $type = trim($_POST['type'] ?? 'percentage');
        $value = (float)($_POST['value'] ?? 0);
        $minAmount = (float)($_POST['min_amount'] ?? 0);
        $maxUses = (int)($_POST['max_uses'] ?? 0);
        $maxUsesPerUser = (int)($_POST['max_uses_per_user'] ?? 1);
        $expiresAt = trim($_POST['expires_at'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validate required fields
        if (empty($code)) {
            $this->setFlash('error', 'Coupon code is required');
            $this->redirect('/admin/coupons/' . $id . '/edit');
            return;
        }

        if ($value <= 0) {
            $this->setFlash('error', 'Coupon value must be greater than 0');
            $this->redirect('/admin/coupons/' . $id . '/edit');
            return;
        }

        // Validate code uniqueness (excluding current coupon)
        if ($this->couponModel->codeExists($code, $id)) {
            $this->setFlash('error', 'Coupon code already exists');
            $this->redirect('/admin/coupons/' . $id . '/edit');
            return;
        }

        // Validate type
        $validTypes = ['percentage', 'fixed', 'bonus'];
        if (!in_array($type, $validTypes)) {
            $type = 'percentage';
        }

        // Validate percentage value
        if ($type === 'percentage' && $value > 100) {
            $this->setFlash('error', 'Percentage discount cannot exceed 100%');
            $this->redirect('/admin/coupons/' . $id . '/edit');
            return;
        }

        // Store old values for audit
        $oldValues = [
            'code' => $coupon['code'],
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'is_active' => $coupon['is_active']
        ];

        // Update coupon
        $this->couponModel->updateCoupon($id, [
            'code' => $code,
            'type' => $type,
            'value' => $value,
            'min_amount' => $minAmount,
            'max_uses' => $maxUses > 0 ? $maxUses : null,
            'max_uses_per_user' => $maxUsesPerUser,
            'expires_at' => !empty($expiresAt) ? $expiresAt : null,
            'description' => $description,
            'is_active' => $isActive
        ]);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            'coupon_updated',
            'coupon',
            $id,
            $oldValues,
            ['code' => $code, 'type' => $type, 'value' => $value, 'is_active' => $isActive],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Coupon updated successfully');
        $this->redirect('/admin/coupons');
    }

    /**
     * Delete (deactivate) a coupon
     */
    public function deleteCoupon(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $coupon = $this->couponModel->find($id);

        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        // Soft delete by deactivating
        $this->couponModel->update($id, [
            'is_active' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            'coupon_deleted',
            'coupon',
            $id,
            ['is_active' => $coupon['is_active']],
            ['is_active' => 0],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Coupon deactivated successfully');
        $this->redirect('/admin/coupons');
    }

    /**
     * Toggle coupon active status
     */
    public function toggleCoupon(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $coupon = $this->couponModel->find($id);

        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        $oldActive = $coupon['is_active'];
        $this->couponModel->toggleActive($id);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            $oldActive ? 'coupon_deactivated' : 'coupon_activated',
            'coupon',
            $id,
            ['is_active' => $oldActive],
            ['is_active' => !$oldActive],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Coupon ' . ($oldActive ? 'deactivated' : 'activated') . ' successfully');
        $this->redirect('/admin/coupons');
    }

    /**
     * Show coupon statistics
     */
    public function couponStats(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $coupon = $this->couponModel->find($id);

        if (!$coupon) {
            $this->setFlash('error', 'Coupon not found');
            $this->redirect('/admin/coupons');
            return;
        }

        $stats = $this->couponModel->getStats($id);
        $usages = $this->couponUsageModel->getByCoupon($id);

        $this->currentPage = 'admin-coupons';
        $this->render('admin/coupon_stats', [
            'pageTitle' => 'Admin - Coupon Stats',
            'currentPage' => $this->currentPage,
            'coupon' => $coupon,
            'stats' => $stats['stats'],
            'usages' => $usages
        ], ['admin'], ['admin']);
    }

    // =====================
    // Changelog Management
    // =====================

    /**
     * List all changelog entries
     */
    public function changelogs(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $changelogs = $this->changelogModel->getAll();
        $stats = $this->changelogModel->getStats();

        $this->currentPage = 'admin-changelogs';
        $this->render('admin/changelogs', [
            'pageTitle' => 'Admin - Changelogs',
            'currentPage' => $this->currentPage,
            'changelogs' => $changelogs,
            'stats' => $stats
        ], ['admin'], ['admin']);
    }

    /**
     * Show create changelog form
     */
    public function createChangelog(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $versions = $this->changelogModel->getVersions();

        $this->currentPage = 'admin-changelogs';
        $this->render('admin/changelog_form', [
            'pageTitle' => 'Admin - Create Changelog',
            'currentPage' => $this->currentPage,
            'changelog' => [],
            'isEdit' => false,
            'versions' => $versions
        ], ['admin'], ['admin']);
    }

    /**
     * Store new changelog entry
     */
    public function storeChangelog(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        
        $version = trim($_POST['version'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? 'feature');
        $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : null;
        $publishNow = isset($_POST['publish_now']);

        // Validation
        if (empty($version) || empty($title)) {
            $this->setFlash('error', 'Version and title are required');
            $this->redirect('/admin/changelogs/create');
            return;
        }

        if (!in_array($type, ['feature', 'fix', 'improvement', 'security'])) {
            $type = 'feature';
        }

        // If publish now is clicked, set published_at to now
        if ($publishNow) {
            $publishedAt = date('Y-m-d H:i:s');
        } elseif ($publishedAt) {
            $publishedAt = date('Y-m-d H:i:s', strtotime($publishedAt));
        }

        $id = $this->changelogModel->createEntry([
            'version' => $version,
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'published_at' => $publishedAt
        ]);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            'changelog_created',
            'changelog',
            $id,
            null,
            ['version' => $version, 'title' => $title, 'type' => $type],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Changelog entry created successfully');
        $this->redirect('/admin/changelogs');
    }

    /**
     * Show edit changelog form
     */
    public function editChangelog(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $changelog = $this->changelogModel->find($id);

        if (!$changelog) {
            $this->setFlash('error', 'Changelog entry not found');
            $this->redirect('/admin/changelogs');
            return;
        }

        $versions = $this->changelogModel->getVersions();

        $this->currentPage = 'admin-changelogs';
        $this->render('admin/changelog_form', [
            'pageTitle' => 'Admin - Edit Changelog',
            'currentPage' => $this->currentPage,
            'changelog' => $changelog,
            'isEdit' => true,
            'versions' => $versions
        ], ['admin'], ['admin']);
    }

    /**
     * Update changelog entry
     */
    public function updateChangelog(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $changelog = $this->changelogModel->find($id);

        if (!$changelog) {
            $this->setFlash('error', 'Changelog entry not found');
            $this->redirect('/admin/changelogs');
            return;
        }

        $version = trim($_POST['version'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = trim($_POST['type'] ?? 'feature');
        $publishedAt = !empty($_POST['published_at']) ? $_POST['published_at'] : null;

        // Validation
        if (empty($version) || empty($title)) {
            $this->setFlash('error', 'Version and title are required');
            $this->redirect('/admin/changelogs/' . $id . '/edit');
            return;
        }

        if (!in_array($type, ['feature', 'fix', 'improvement', 'security'])) {
            $type = 'feature';
        }

        if ($publishedAt) {
            $publishedAt = date('Y-m-d H:i:s', strtotime($publishedAt));
        }

        $this->changelogModel->updateEntry($id, [
            'version' => $version,
            'title' => $title,
            'description' => $description,
            'type' => $type,
            'published_at' => $publishedAt
        ]);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            'changelog_updated',
            'changelog',
            $id,
            ['version' => $changelog['version'], 'title' => $changelog['title']],
            ['version' => $version, 'title' => $title, 'type' => $type],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Changelog entry updated successfully');
        $this->redirect('/admin/changelogs');
    }

    /**
     * Delete changelog entry
     */
    public function deleteChangelog(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $changelog = $this->changelogModel->find($id);

        if (!$changelog) {
            $this->setFlash('error', 'Changelog entry not found');
            $this->redirect('/admin/changelogs');
            return;
        }

        $this->changelogModel->delete($id);

        // Log audit
        $this->auditLogModel->logAction(
            $admin['id'],
            'changelog_deleted',
            'changelog',
            $id,
            ['version' => $changelog['version'], 'title' => $changelog['title']],
            null,
            $this->getClientIP()
        );

        $this->setFlash('success', 'Changelog entry deleted successfully');
        $this->redirect('/admin/changelogs');
    }

    // =====================
    // Email Templates Management
    // =====================

    /**
     * List all email templates
     */
    public function emailTemplates(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $emailTemplateModel = new EmailTemplate();
        $templates = $emailTemplateModel->getAll();
        $stats = $emailTemplateModel->getStats();

        $this->currentPage = 'admin-email-templates';
        $this->render('admin/email_templates', [
            'pageTitle' => 'Admin - Email Templates',
            'currentPage' => $this->currentPage,
            'templates' => $templates,
            'stats' => $stats
        ], ['admin'], ['admin']);
    }

    /**
     * Show edit email template form
     */
    public function editEmailTemplate(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $emailTemplateModel = new EmailTemplate();
        $template = $emailTemplateModel->find($id);

        if (!$template) {
            $this->setFlash('error', 'Email template not found');
            $this->redirect('/admin/email-templates');
            return;
        }

        $this->currentPage = 'admin-email-templates';
        $this->render('admin/email_template_form', [
            'pageTitle' => 'Admin - Edit Email Template',
            'currentPage' => $this->currentPage,
            'template' => $template
        ], ['admin'], ['admin']);
    }

    /**
     * Update email template
     */
    public function updateEmailTemplate(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $emailTemplateModel = new EmailTemplate();
        $template = $emailTemplateModel->find($id);

        if (!$template) {
            $this->setFlash('error', 'Email template not found');
            $this->redirect('/admin/email-templates');
            return;
        }

        $subject = trim($_POST['subject'] ?? '');
        $body = $_POST['body'] ?? '';
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if (empty($subject) || empty($body)) {
            $this->setFlash('error', 'Subject and body are required');
            $this->redirect('/admin/email-templates/' . $id . '/edit');
            return;
        }

        $emailTemplateModel->updateTemplate($id, [
            'subject' => $subject,
            'body' => $body,
            'is_active' => $isActive
        ]);

        $this->auditLogModel->logAction(
            $admin['id'],
            'email_template_updated',
            'email_template',
            $id,
            ['subject' => $template['subject']],
            ['subject' => $subject],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Email template updated successfully');
        $this->redirect('/admin/email-templates');
    }

    /**
     * Reset email template to default
     */
    public function resetEmailTemplate(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $emailTemplateModel = new EmailTemplate();
        $template = $emailTemplateModel->find($id);

        if (!$template) {
            $this->setFlash('error', 'Email template not found');
            $this->redirect('/admin/email-templates');
            return;
        }

        $result = $emailTemplateModel->resetToDefault($id);

        if ($result) {
            $this->auditLogModel->logAction(
                $admin['id'],
                'email_template_reset',
                'email_template',
                $id,
                null,
                ['name' => $template['name']],
                $this->getClientIP()
            );
            $this->setFlash('success', 'Email template reset to default');
        } else {
            $this->setFlash('error', 'Could not reset template. Default not found.');
        }

        $this->redirect('/admin/email-templates');
    }

    // =====================
    // Incident Management
    // =====================

    /**
     * List all incidents
     */
    public function incidents(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $incidentModel = new Incident();
        $incidents = $incidentModel->getAll();
        $stats = $incidentModel->getStats();

        $this->currentPage = 'admin-incidents';
        $this->render('admin/incidents', [
            'pageTitle' => 'Admin - Incidents',
            'currentPage' => $this->currentPage,
            'incidents' => $incidents,
            'stats' => $stats
        ], ['admin'], ['admin']);
    }

    /**
     * Show create incident form
     */
    public function createIncident(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $this->currentPage = 'admin-incidents';
        $this->render('admin/incident_form', [
            'pageTitle' => 'Admin - Report Incident',
            'currentPage' => $this->currentPage,
            'incident' => null
        ], ['admin'], ['admin']);
    }

    /**
     * Store new incident
     */
    public function storeIncident(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $severity = trim($_POST['severity'] ?? 'minor');
        $status = trim($_POST['status'] ?? 'investigating');
        $affectedComponents = $_POST['affected_components'] ?? [];
        $startedAt = !empty($_POST['started_at']) ? $_POST['started_at'] : null;

        if (empty($title)) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/admin/incidents/create');
            return;
        }

        if (!in_array($severity, ['minor', 'major', 'critical'])) {
            $severity = 'minor';
        }

        if (!in_array($status, ['investigating', 'identified', 'monitoring', 'resolved'])) {
            $status = 'investigating';
        }

        $incidentModel = new Incident();
        $id = $incidentModel->createIncident([
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'status' => $status,
            'affected_components' => $affectedComponents,
            'started_at' => $startedAt
        ]);

        $this->auditLogModel->logAction(
            $admin['id'],
            'incident_created',
            'incident',
            $id,
            null,
            ['title' => $title, 'severity' => $severity],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Incident reported successfully');
        $this->redirect('/admin/incidents');
    }

    /**
     * Show edit incident form
     */
    public function editIncident(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $incidentModel = new Incident();
        $incident = $incidentModel->find($id);

        if (!$incident) {
            $this->setFlash('error', 'Incident not found');
            $this->redirect('/admin/incidents');
            return;
        }

        $this->currentPage = 'admin-incidents';
        $this->render('admin/incident_form', [
            'pageTitle' => 'Admin - Edit Incident',
            'currentPage' => $this->currentPage,
            'incident' => $incident
        ], ['admin'], ['admin']);
    }

    /**
     * Update incident
     */
    public function updateIncident(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $incidentModel = new Incident();
        $incident = $incidentModel->find($id);

        if (!$incident) {
            $this->setFlash('error', 'Incident not found');
            $this->redirect('/admin/incidents');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $severity = trim($_POST['severity'] ?? 'minor');
        $status = trim($_POST['status'] ?? 'investigating');
        $affectedComponents = $_POST['affected_components'] ?? [];
        $updateMessage = trim($_POST['update_message'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Title is required');
            $this->redirect('/admin/incidents/' . $id . '/edit');
            return;
        }

        $incidentModel->updateIncident($id, [
            'title' => $title,
            'description' => $description,
            'severity' => $severity,
            'status' => $status,
            'affected_components' => $affectedComponents
        ]);

        // Add update to timeline if provided
        if (!empty($updateMessage)) {
            $incidentModel->addUpdate($id, $updateMessage, $status);
        }

        // If status changed to resolved, set resolved_at
        if ($status === 'resolved' && $incident['status'] !== 'resolved') {
            $incidentModel->resolve($id);
        }

        $this->auditLogModel->logAction(
            $admin['id'],
            'incident_updated',
            'incident',
            $id,
            ['status' => $incident['status']],
            ['status' => $status, 'title' => $title],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Incident updated successfully');
        $this->redirect('/admin/incidents');
    }

    /**
     * Resolve incident
     */
    public function resolveIncident(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $incidentModel = new Incident();
        $incident = $incidentModel->find($id);

        if (!$incident) {
            $this->setFlash('error', 'Incident not found');
            $this->redirect('/admin/incidents');
            return;
        }

        $incidentModel->resolve($id);
        $incidentModel->addUpdate($id, 'This incident has been resolved.', 'resolved');

        $this->auditLogModel->logAction(
            $admin['id'],
            'incident_resolved',
            'incident',
            $id,
            ['status' => $incident['status']],
            ['status' => 'resolved'],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Incident marked as resolved');
        $this->redirect('/admin/incidents');
    }

    /**
     * Delete incident
     */
    public function deleteIncident(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $incidentModel = new Incident();
        $incident = $incidentModel->find($id);

        if (!$incident) {
            $this->setFlash('error', 'Incident not found');
            $this->redirect('/admin/incidents');
            return;
        }

        $incidentModel->delete($id);

        $this->auditLogModel->logAction(
            $admin['id'],
            'incident_deleted',
            'incident',
            $id,
            ['title' => $incident['title']],
            null,
            $this->getClientIP()
        );

        $this->setFlash('success', 'Incident deleted');
        $this->redirect('/admin/incidents');
    }

    // =====================
    // Role Management
    // =====================

    /**
     * List all admin roles
     */
    public function roles(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $permissionService = new PermissionService();

        if (!$permissionService->canManageRoles($admin)) {
            $this->setFlash('error', 'You do not have permission to manage roles.');
            $this->redirect('/admin');
            return;
        }

        $roleModel = new AdminRole();
        $roles = $roleModel->getAll();
        $userCounts = $roleModel->getUserCounts();

        $this->currentPage = 'admin-roles';
        $this->render('admin/roles', [
            'pageTitle' => 'Admin - Roles',
            'currentPage' => $this->currentPage,
            'roles' => $roles,
            'userCounts' => $userCounts
        ], ['admin'], ['admin']);
    }

    /**
     * Show create role form
     */
    public function createRole(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $permissionService = new PermissionService();

        if (!$permissionService->canManageRoles($admin)) {
            $this->setFlash('error', 'You do not have permission to manage roles.');
            $this->redirect('/admin');
            return;
        }

        $this->currentPage = 'admin-roles';
        $this->render('admin/role_form', [
            'pageTitle' => 'Admin - Create Role',
            'currentPage' => $this->currentPage,
            'role' => null,
            'availablePermissions' => AdminRole::getAvailablePermissions(),
            'isEdit' => false
        ], ['admin'], ['admin']);
    }

    /**
     * Store a new role
     */
    public function storeRole(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $permissionService = new PermissionService();

        if (!$permissionService->canManageRoles($admin)) {
            $this->setFlash('error', 'You do not have permission to manage roles.');
            $this->redirect('/admin');
            return;
        }

        $roleModel = new AdminRole();
        
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        if (empty($name)) {
            $this->setFlash('error', 'Role name is required.');
            $this->redirect('/admin/roles/create');
            return;
        }

        // Validate name format (lowercase, underscores only)
        if (!preg_match('/^[a-z_]+$/', $name)) {
            $this->setFlash('error', 'Role name must contain only lowercase letters and underscores.');
            $this->redirect('/admin/roles/create');
            return;
        }

        // Check if name already exists
        $existing = $roleModel->getByName($name);
        if ($existing) {
            $this->setFlash('error', 'A role with this name already exists.');
            $this->redirect('/admin/roles/create');
            return;
        }

        // Filter permissions to only valid ones
        $validPermissions = AdminRole::getAllPermissionKeys();
        $permissions = array_intersect($permissions, $validPermissions);

        $roleId = $roleModel->createRole([
            'name' => $name,
            'description' => $description,
            'permissions' => $permissions,
            'is_system' => 0
        ]);

        $this->auditLogModel->logAction(
            $admin['id'],
            'role_created',
            'role',
            $roleId,
            null,
            ['name' => $name, 'permissions_count' => count($permissions)],
            $this->getClientIP()
        );

        $this->setFlash('success', 'Role created successfully.');
        $this->redirect('/admin/roles');
    }

    /**
     * Show edit role form
     */
    public function editRole(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $permissionService = new PermissionService();

        if (!$permissionService->canManageRoles($admin)) {
            $this->setFlash('error', 'You do not have permission to manage roles.');
            $this->redirect('/admin');
            return;
        }

        $roleModel = new AdminRole();
        $role = $roleModel->find($id);

        if (!$role) {
            $this->setFlash('error', 'Role not found.');
            $this->redirect('/admin/roles');
            return;
        }

        $this->currentPage = 'admin-roles';
        $this->render('admin/role_form', [
            'pageTitle' => 'Admin - Edit Role',
            'currentPage' => $this->currentPage,
            'role' => $role,
            'availablePermissions' => AdminRole::getAvailablePermissions(),
            'isEdit' => true
        ], ['admin'], ['admin']);
    }

    /**
     * Update a role
     */
    public function updateRole(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $permissionService = new PermissionService();

        if (!$permissionService->canManageRoles($admin)) {
            $this->setFlash('error', 'You do not have permission to manage roles.');
            $this->redirect('/admin');
            return;
        }

        $roleModel = new AdminRole();
        $role = $roleModel->find($id);

        if (!$role) {
            $this->setFlash('error', 'Role not found.');
            $this->redirect('/admin/roles');
            return;
        }

        $description = trim($_POST['description'] ?? '');
        $permissions = $_POST['permissions'] ?? [];

        // For system roles, don't allow changing the name
        $name = $role['is_system'] ? $role['name'] : trim($_POST['name'] ?? $role['name']);

        // Filter permissions to only valid ones
        $validPermissions = AdminRole::getAllPermissionKeys();
        $permissions = array_intersect($permissions, $validPermissions);

        $oldPermissions = json_decode($role['permissions'] ?? '[]', true) ?: [];

        $roleModel->updateRole($id, [
            'name' => $name,
            'description' => $description,
            'permissions' => $permissions
        ]);

        $this->auditLogModel->logAction(
            $admin['id'],
            'role_updated',
            'role',
            $id,
            ['permissions_count' => count($oldPermissions)],
            ['name' => $name, 'permissions_count' => count($permissions)],
            $this->getClientIP()
        );

        // Clear permission cache
        $permissionService->clearCache();

        $this->setFlash('success', 'Role updated successfully.');
        $this->redirect('/admin/roles');
    }

    /**
     * Delete a role
     */
    public function deleteRole(int $id): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $permissionService = new PermissionService();

        if (!$permissionService->canManageRoles($admin)) {
            $this->setFlash('error', 'You do not have permission to manage roles.');
            $this->redirect('/admin');
            return;
        }

        $roleModel = new AdminRole();
        $role = $roleModel->find($id);

        if (!$role) {
            $this->setFlash('error', 'Role not found.');
            $this->redirect('/admin/roles');
            return;
        }

        if ($role['is_system']) {
            $this->setFlash('error', 'System roles cannot be deleted.');
            $this->redirect('/admin/roles');
            return;
        }

        $roleModel->deleteRole($id);

        $this->auditLogModel->logAction(
            $admin['id'],
            'role_deleted',
            'role',
            $id,
            ['name' => $role['name']],
            null,
            $this->getClientIP()
        );

        $this->setFlash('success', 'Role deleted successfully.');
        $this->redirect('/admin/roles');
    }
}
