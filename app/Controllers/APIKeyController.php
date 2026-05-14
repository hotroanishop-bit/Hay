<?php
/**
 * APIKey Controller
 * Handles API key CRUD operations
 */

class APIKeyController extends BaseController
{
    private AuthService $authService;
    private APIService $apiService;
    private AuditService $auditService;
    private ApiKey $apiKeyModel;
    private ProxyService $proxyService;
    private IPWhitelistService $ipWhitelistService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        
        $this->apiKeyModel = new ApiKey();
        $usageLogModel = new UsageLog();
        
        $this->apiService = new APIService($this->apiKeyModel, $usageLogModel);
        $this->auditService = new AuditService();
        $this->proxyService = new ProxyService();
        $this->ipWhitelistService = new IPWhitelistService();
    }

    /**
     * List all API keys for the user
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $apiKeys = $this->apiKeyModel->findByUser($user['id']);
        $providers = $this->apiService->listProviders();

        $this->currentPage = 'keys';
        $this->render('keys/index', [
            'pageTitle' => 'API Keys',
            'currentPage' => $this->currentPage,
            'apiKeys' => $apiKeys,
            'providers' => $providers
        ], ['keys'], ['keys']);
    }

    /**
     * Show create API key form
     */
    public function create(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $providers = $this->apiService->listProviders();
        $availableModels = $this->proxyService->getMappedModels();

        $this->currentPage = 'keys';
        $this->render('keys/create', [
            'pageTitle' => 'Create API Key',
            'currentPage' => $this->currentPage,
            'providers' => $providers,
            'availableModels' => $availableModels
        ], ['keys'], ['keys']);
    }

    /**
     * Store a new API key
     */
    public function store(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $provider = $_POST['provider'] ?? null;
        $model = $_POST['model'] ?? null;
        $rateLimit = isset($_POST['rate_limit']) ? (int)$_POST['rate_limit'] : null;
        $usageLimit = isset($_POST['usage_limit']) ? (int)$_POST['usage_limit'] : null;
        $expiresAt = $_POST['expires_at'] ?? null;
        
        // Handle allowed_models - array of selected models
        $allowedModels = $_POST['allowed_models'] ?? [];
        if (!is_array($allowedModels)) {
            $allowedModels = [];
        }
        // Filter to only valid models
        $validModels = $this->proxyService->getMappedModels();
        $allowedModels = array_filter($allowedModels, function($m) use ($validModels) {
            return in_array($m, $validModels, true);
        });
        
        // Handle allowed_ips - parse from textarea
        $allowedIpsRaw = trim($_POST['allowed_ips'] ?? '');
        $allowedIps = [];
        if (!empty($allowedIpsRaw)) {
            $allowedIps = $this->ipWhitelistService->parseIPList($allowedIpsRaw);
        }

        // Basic validation
        if (empty($name)) {
            $this->setFlash('error', 'API key name is required');
            $this->redirect('/keys/create');
            return;
        }

        try {
            $result = $this->apiService->createKey($user['id'], [
                'name' => $name,
                'provider' => $provider,
                'model' => $model,
                'rate_limit' => $rateLimit,
                'usage_limit' => $usageLimit,
                'expires_at' => $expiresAt,
                'allowed_models' => !empty($allowedModels) ? $allowedModels : null,
                'allowed_ips' => !empty($allowedIps) ? $allowedIps : null
            ]);

            $this->auditService->log($user['id'], 'api_key_created', [
                'key_id' => $result['id'],
                'name' => $name,
                'allowed_models' => $allowedModels,
                'allowed_ips' => $allowedIps
            ]);

            // Store the plain key temporarily for display
            $_SESSION['new_api_key'] = $result['key'];

            $this->setFlash('success', 'API key created successfully. Make sure to copy it now - it won\'t be shown again.');
            $this->redirect('/keys');
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to create API key: ' . $e->getMessage());
            $this->redirect('/keys/create');
        }
    }

    /**
     * Show a specific API key details
     */
    public function show(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $apiKey = $this->apiKeyModel->find($id);

        // Check ownership
        if (!$apiKey || $apiKey['user_id'] !== $user['id']) {
            $this->setFlash('error', 'API key not found');
            $this->redirect('/keys');
            return;
        }

        // Get key statistics
        $stats = $this->apiService->getKeyStats($id);
        
        // Get allowed models and IPs for display
        $allowedModels = $this->apiKeyModel->getAllowedModels($apiKey);
        $allowedIPs = $this->apiKeyModel->getAllowedIPs($apiKey);
        $availableModels = $this->proxyService->getMappedModels();

        $this->currentPage = 'keys';
        $this->render('keys/show', [
            'pageTitle' => 'API Key Details',
            'currentPage' => $this->currentPage,
            'apiKey' => $apiKey,
            'stats' => $stats,
            'allowedModels' => $allowedModels,
            'allowedIPs' => $allowedIPs,
            'availableModels' => $availableModels
        ], ['keys'], ['keys']);
    }

    /**
     * Rotate an API key
     */
    public function rotate(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $apiKey = $this->apiKeyModel->find($id);

        // Check ownership
        if (!$apiKey || $apiKey['user_id'] !== $user['id']) {
            $this->setFlash('error', 'API key not found');
            $this->redirect('/keys');
            return;
        }

        try {
            $result = $this->apiService->rotateKey($id);

            if ($result) {
                $this->auditService->log($user['id'], 'api_key_rotated', [
                    'old_key_id' => $id,
                    'new_key_id' => $result['new_key_id']
                ]);

                // Store the new plain key temporarily for display
                $_SESSION['new_api_key'] = $result['new_key'];

                $this->setFlash('success', 'API key rotated successfully. Make sure to copy the new key.');
            } else {
                $this->setFlash('error', 'Failed to rotate API key');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to rotate API key: ' . $e->getMessage());
        }

        $this->redirect('/keys');
    }

    /**
     * Revoke (deactivate) an API key
     */
    public function revoke(int $id): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $apiKey = $this->apiKeyModel->find($id);

        // Check ownership
        if (!$apiKey || $apiKey['user_id'] !== $user['id']) {
            $this->setFlash('error', 'API key not found');
            $this->redirect('/keys');
            return;
        }

        try {
            $result = $this->apiService->revokeKey($id);

            if ($result) {
                $this->auditService->log($user['id'], 'api_key_revoked', [
                    'key_id' => $id,
                    'name' => $apiKey['name']
                ]);

                $this->setFlash('success', 'API key revoked successfully');
            } else {
                $this->setFlash('error', 'Failed to revoke API key');
            }
        } catch (Exception $e) {
            $this->setFlash('error', 'Failed to revoke API key: ' . $e->getMessage());
        }

        $this->redirect('/keys');
    }
}
