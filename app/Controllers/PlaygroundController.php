<?php
/**
 * Playground Controller
 * Interactive API testing page for users to test their API keys
 */

class PlaygroundController extends BaseController
{
    private AuthService $authService;
    private ApiKey $apiKeyModel;
    private ModelPricing $modelPricingModel;
    private ProxyService $proxyService;
    private UsageLog $usageLogModel;
    private CreditService $creditService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->apiKeyModel = new ApiKey();
        $this->modelPricingModel = new ModelPricing();
        $this->proxyService = new ProxyService();
        $this->usageLogModel = new UsageLog();
        $this->creditService = new CreditService($userModel, new Transaction());
    }

    /**
     * Show the API Playground UI
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $userId = $user['id'];

        // Get user's active API keys
        $allKeys = $this->apiKeyModel->findByUser($userId);
        $activeKeys = array_filter($allKeys, function($key) {
            return $key['is_active'] == 1;
        });

        // Get available models (mapped models from proxy service)
        $mappedModels = $this->proxyService->getMappedModels();
        
        // Get model pricing for cost estimation
        $modelPricing = [];
        foreach ($mappedModels as $modelName) {
            $pricing = $this->modelPricingModel->findByModel($modelName);
            if ($pricing) {
                $modelPricing[$modelName] = [
                    'input_price_per_1k' => (float)$pricing['input_price_per_1k'],
                    'output_price_per_1k' => (float)$pricing['output_price_per_1k']
                ];
            } else {
                // Default pricing if not found
                $modelPricing[$modelName] = [
                    'input_price_per_1k' => 0.001,
                    'output_price_per_1k' => 0.002
                ];
            }
        }

        // Get user's current balance
        $balance = $this->creditService->getBalance($userId);

        $this->currentPage = 'playground';
        $this->render('playground/index', [
            'pageTitle' => 'API Playground',
            'currentPage' => $this->currentPage,
            'user' => $user,
            'apiKeys' => array_values($activeKeys),
            'models' => $mappedModels,
            'modelPricing' => $modelPricing,
            'balance' => $balance
        ], ['playground'], ['playground']);
    }

    /**
     * Execute an API call from the playground (AJAX endpoint)
     */
    public function execute(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $userId = $user['id'];

        // Get request data
        $input = json_decode(file_get_contents('php://input'), true);
        
        if (!$input) {
            $this->json(['error' => 'Invalid JSON input'], 400);
            return;
        }

        // Validate required fields
        $apiKeyId = $input['api_key_id'] ?? null;
        $model = $input['model'] ?? null;
        $userMessage = $input['user_message'] ?? null;

        if (!$apiKeyId || !$model || !$userMessage) {
            $this->json([
                'error' => 'Missing required fields: api_key_id, model, user_message'
            ], 400);
            return;
        }

        // Validate user owns the API key
        $apiKey = $this->apiKeyModel->find($apiKeyId);
        if (!$apiKey || $apiKey['user_id'] != $userId) {
            $this->json(['error' => 'API key not found or not owned by user'], 403);
            return;
        }

        // Check if key is active
        if (!$apiKey['is_active']) {
            $this->json(['error' => 'API key is not active'], 403);
            return;
        }

        // Validate model is allowed for this key
        if (!$this->apiKeyModel->isModelAllowed($apiKey, $model)) {
            $this->json(['error' => 'This API key does not have access to the selected model'], 403);
            return;
        }

        // Validate model exists
        if (!$this->proxyService->isValidModel($model)) {
            $this->json(['error' => 'Invalid model selected'], 400);
            return;
        }

        // Check user's balance
        $balance = $this->creditService->getBalance($userId);
        if ($balance <= 0) {
            $this->json(['error' => 'Insufficient balance. Please add credits to continue.'], 402);
            return;
        }

        // Build the request
        $systemPrompt = $input['system_prompt'] ?? null;
        $temperature = isset($input['temperature']) ? (float)$input['temperature'] : 1.0;
        $maxTokens = isset($input['max_tokens']) ? (int)$input['max_tokens'] : 1024;

        // Clamp values
        $temperature = max(0, min(2, $temperature));
        $maxTokens = max(1, min(4096, $maxTokens));

        // Build messages array
        $messages = [];
        if ($systemPrompt && trim($systemPrompt)) {
            $messages[] = [
                'role' => 'system',
                'content' => trim($systemPrompt)
            ];
        }
        $messages[] = [
            'role' => 'user',
            'content' => trim($userMessage)
        ];

        // Build request data for proxy
        $requestData = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => $temperature,
            'max_tokens' => $maxTokens
        ];

        try {
            // Forward request through proxy service
            $startTime = microtime(true);
            $response = $this->proxyService->forwardRequest($requestData, $model, false);
            $endTime = microtime(true);
            $latency = round(($endTime - $startTime) * 1000); // ms

            // Check for error in response
            if (isset($response['error'])) {
                $this->json([
                    'success' => false,
                    'error' => $response['error']['message'] ?? 'An error occurred',
                    'error_type' => $response['error']['type'] ?? 'api_error'
                ], 500);
                return;
            }

            // Extract usage information
            $inputTokens = $response['usage']['prompt_tokens'] ?? 0;
            $outputTokens = $response['usage']['completion_tokens'] ?? 0;
            $totalTokens = $response['usage']['total_tokens'] ?? ($inputTokens + $outputTokens);

            // Calculate cost
            $cost = $this->modelPricingModel->calculateCost($model, $inputTokens, $outputTokens);

            // Deduct cost from user's balance
            if ($cost > 0) {
                $this->creditService->deduct($userId, $cost, 'API Playground usage - ' . $model);
            }

            // Log usage
            $this->usageLogModel->create([
                'user_id' => $userId,
                'api_key_id' => $apiKeyId,
                'model' => $model,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'total_tokens' => $totalTokens,
                'cost' => $cost,
                'latency' => $latency,
                'status' => 'success',
                'endpoint' => '/playground/execute',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Increment API key usage count
            $this->apiKeyModel->incrementUsage($apiKeyId);

            // Extract response text
            $responseText = '';
            if (isset($response['choices'][0]['message']['content'])) {
                $responseText = $response['choices'][0]['message']['content'];
            } elseif (isset($response['choices'][0]['text'])) {
                $responseText = $response['choices'][0]['text'];
            }

            // Return success response
            $this->json([
                'success' => true,
                'response' => $response,
                'response_text' => $responseText,
                'usage' => [
                    'input_tokens' => $inputTokens,
                    'output_tokens' => $outputTokens,
                    'total_tokens' => $totalTokens
                ],
                'cost' => $cost,
                'latency_ms' => $latency,
                'new_balance' => $this->creditService->getBalance($userId)
            ]);

        } catch (InvalidArgumentException $e) {
            $this->json([
                'success' => false,
                'error' => $e->getMessage(),
                'error_type' => 'invalid_request'
            ], 400);
        } catch (Exception $e) {
            // Log error
            error_log('Playground execute error: ' . $e->getMessage());
            
            $this->json([
                'success' => false,
                'error' => 'An unexpected error occurred. Please try again.',
                'error_type' => 'server_error'
            ], 500);
        }
    }
}
