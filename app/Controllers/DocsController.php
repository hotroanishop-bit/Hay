<?php
/**
 * DocsController
 * Handles API documentation page
 */

class DocsController extends BaseController
{
    protected $currentPage = 'docs';

    private ?AuthService $authService;
    private ModelPricing $modelPricing;
    private Plan $planModel;
    private ApiKey $apiKeyModel;

    public function __construct(
        ?AuthService $authService = null,
        ?ModelPricing $modelPricing = null,
        ?Plan $planModel = null,
        ?ApiKey $apiKeyModel = null
    ) {
        $this->authService = $authService;
        $this->modelPricing = $modelPricing ?? new ModelPricing();
        $this->planModel = $planModel ?? new Plan();
        $this->apiKeyModel = $apiKeyModel ?? new ApiKey();
    }

    /**
     * Display the API documentation page
     */
    public function index(): void
    {
        // Get logged in user if available (for personalized examples)
        $user = null;
        $userApiKey = null;
        
        if ($this->authService && $this->authService->check()) {
            $user = $this->authService->user();
            
            // Get user's first active API key for personalized examples
            if ($user) {
                $userKeys = $this->apiKeyModel->findByUser($user['id']);
                foreach ($userKeys as $key) {
                    if (!empty($key['is_active'])) {
                        $userApiKey = $key;
                        break;
                    }
                }
            }
        }

        // Get all active models with pricing
        $models = $this->modelPricing->findAllActive();

        // Get all active plans for rate limit info
        $plans = $this->planModel->findActive();

        // Get base URL for API
        $baseUrl = $this->getBaseUrl();

        $this->render('docs/index', [
            'pageTitle' => 'API Documentation',
            'user' => $user,
            'userApiKey' => $userApiKey,
            'models' => $models,
            'plans' => $plans,
            'baseUrl' => $baseUrl
        ], ['docs'], ['docs']);
    }

    /**
     * Get the base URL for API endpoints
     */
    private function getBaseUrl(): string
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'your-domain.com';
        return $protocol . '://' . $host . '/api/v1';
    }
}
