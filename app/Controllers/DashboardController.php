<?php
/**
 * Dashboard Controller
 * Handles main dashboard view with stats and overview
 */

class DashboardController extends BaseController
{
    private AuthService $authService;
    private APIService $apiService;
    private CreditService $creditService;
    private UsageLog $usageLogModel;
    private ApiKey $apiKeyModel;
    private Transaction $transactionModel;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->apiKeyModel = new ApiKey();
        $this->usageLogModel = new UsageLog();
        $this->transactionModel = new Transaction();
        
        $this->apiService = new APIService($this->apiKeyModel, $this->usageLogModel);
        $this->creditService = new CreditService($userModel, $this->transactionModel);
    }

    /**
     * Show main dashboard
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // Get user's balance
        $balance = $this->creditService->getBalance($userId);

        // Get API key statistics
        $apiKeys = $this->apiKeyModel->findByUser($userId);
        $activeKeys = array_filter($apiKeys, fn($key) => $key['is_active']);

        // Get usage statistics
        $usageStats = $this->usageLogModel->getStatsByUser($userId);
        $dailyStats = $this->usageLogModel->getDailyStats($userId, 7);

        // Get recent transactions
        $recentTransactions = $this->transactionModel->getRecent($userId, 5);

        // Get recent usage logs
        $recentUsage = $this->usageLogModel->getRecentByUser($userId, 10);

        $this->currentPage = 'dashboard';
        $this->render('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'currentPage' => $this->currentPage,
            'user' => $user,
            'balance' => $balance,
            'totalKeys' => count($apiKeys),
            'activeKeys' => count($activeKeys),
            'usageStats' => $usageStats,
            'dailyStats' => $dailyStats,
            'recentTransactions' => $recentTransactions,
            'recentUsage' => $recentUsage
        ], ['dashboard', 'analytics'], ['dashboard']);
    }
}
