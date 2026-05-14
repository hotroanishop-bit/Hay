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
