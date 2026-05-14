<?php
/**
 * API Proxy Controller
 * Handles OpenAI-compatible API endpoints with model mapping, billing, and usage logging
 */

class ApiProxyController extends BaseController
{
    private ProxyService $proxyService;
    private TokenCounterService $tokenCounterService;
    private PlanService $planService;
    private CreditService $creditService;
    private PlanSubscriptionService $planSubscriptionService;
    private UsageLog $usageLogModel;
    private ApiKey $apiKeyModel;

    public function __construct()
    {
        $userModel = new User();
        $planModel = new Plan();
        $userPlanModel = new UserPlan();
        
        $this->proxyService = new ProxyService();
        $this->tokenCounterService = new TokenCounterService();
        $this->planService = new PlanService($planModel, new UsageLog());
        $this->creditService = new CreditService($userModel, new Transaction(), new ModelPricing());
        $this->planSubscriptionService = new PlanSubscriptionService($userModel, $planModel, $userPlanModel);
        $this->usageLogModel = new UsageLog();
        $this->apiKeyModel = new ApiKey();
    }

    /**
     * Output JSON response with proper headers
     * 
     * @param array $data Data to encode as JSON
     * @param int $status HTTP status code
     */
    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Output OpenAI-compatible error response
     * 
     * @param string $message Error message
     * @param int $status HTTP status code
     * @param string $type Error type
     */
    protected function error(string $message, int $status, string $type = 'invalid_request_error'): void
    {
        $this->json([
            'error' => [
                'message' => $message,
                'type' => $type,
                'param' => null,
                'code' => $type,
            ]
        ], $status);
    }

    /**
     * Get authenticated API key data from middleware context
     * 
     * @return array API key data
     */
    protected function getAuthenticatedKey(): array
    {
        return $_SERVER['API_KEY'] ?? [];
    }

    /**
     * Parse JSON request body
     * 
     * @return array Parsed request data
     */
    protected function getRequestBody(): array
    {
        $input = file_get_contents('php://input');
        if (empty($input)) {
            return [];
        }
        
        $data = json_decode($input, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [];
        }
        
        return $data;
    }

    /**
     * Handle POST /v1/chat/completions
     * OpenAI-compatible chat completions endpoint
     */
    public function chatCompletions(): void
    {
        $startTime = microtime(true);
        $requestId = $this->generateRequestId();
        
        // Get authenticated key info
        $apiKey = $this->getAuthenticatedKey();
        $apiKeyId = (int) ($_SERVER['API_KEY_ID'] ?? 0);
        $userId = (int) ($_SERVER['API_USER_ID'] ?? 0);
        
        if (!$apiKeyId || !$userId) {
            $this->error('Unauthorized', 401, 'authentication_error');
        }
        
        // Parse request body
        $body = $this->getRequestBody();
        if (empty($body)) {
            $this->error('Invalid JSON in request body', 400);
        }
        
        // Extract parameters
        $model = $body['model'] ?? '';
        $messages = $body['messages'] ?? [];
        $stream = (bool) ($body['stream'] ?? false);
        
        // Validate model
        if (empty($model)) {
            $this->error('Model is required', 400);
        }
        
        if (!$this->proxyService->isValidModel($model)) {
            $this->error('Invalid model: ' . $model, 400, 'model_not_found');
        }
        
        // Validate messages
        if (empty($messages) || !is_array($messages)) {
            $this->error('Messages array is required', 400);
        }
        
        // Get billing type from header or user preference
        $billingType = $this->planSubscriptionService->getBillingType(
            $userId, 
            $_SERVER['HTTP_X_BILLING_TYPE'] ?? null
        );
        
        // Check and reset daily tokens for free plan users
        $this->planSubscriptionService->checkAndResetDailyTokens($userId);
        
        // Estimate input tokens
        $estimatedInputTokens = $this->tokenCounterService->countChatTokens($messages, $model);
        
        // Check daily token limit (for free plan users)
        if (!$this->planSubscriptionService->checkDailyTokenLimit($userId, $estimatedInputTokens)) {
            $this->error('Daily token limit exceeded', 429, 'rate_limit_error');
        }
        
        // Check balance based on billing type BEFORE making the request
        $estimatedOutputTokens = 500; // Conservative estimate for pre-flight
        if ($billingType === 'plan') {
            if (!$this->planSubscriptionService->hasSufficientPlanTokens($userId, $estimatedInputTokens + $estimatedOutputTokens)) {
                $this->error('Insufficient plan tokens', 402, 'insufficient_balance');
            }
        } else {
            // PAYG billing - check credit balance
            if (!$this->creditService->checkSufficientBalanceForRequest($userId, $model, $estimatedInputTokens, $estimatedOutputTokens)) {
                $this->error('Insufficient PAYG balance', 402, 'insufficient_balance');
            }
        }
        
        // Get price multiplier for user's plan
        $priceMultiplier = $this->planService->getPriceMultiplier($userId);
        
        if ($stream) {
            $this->handleStreamingChatCompletions($body, $model, $apiKeyId, $userId, $priceMultiplier, $estimatedInputTokens, $startTime, $requestId, $billingType);
        } else {
            $this->handleNonStreamingChatCompletions($body, $model, $apiKeyId, $userId, $priceMultiplier, $startTime, $requestId, $billingType);
        }
    }

    /**
     * Handle non-streaming chat completions
     */
    private function handleNonStreamingChatCompletions(
        array $body,
        string $model,
        int $apiKeyId,
        int $userId,
        float $priceMultiplier,
        float $startTime,
        string $requestId,
        string $billingType = 'payg'
    ): void {
        // Forward request to upstream
        $response = $this->proxyService->forwardRequest($body, $model, false);
        
        // Calculate response time
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        
        // Check for error response
        if (isset($response['error'])) {
            $this->logUsage($apiKeyId, $userId, '/v1/chat/completions', 0, 0, 0, $responseTimeMs, $model, $requestId, 0, $billingType);
            $this->json($response, $this->getErrorStatusCode($response['error']['type'] ?? 'api_error'));
        }
        
        // Extract usage from response
        $promptTokens = $response['usage']['prompt_tokens'] ?? 0;
        $completionTokens = $response['usage']['completion_tokens'] ?? 0;
        $totalTokens = $response['usage']['total_tokens'] ?? ($promptTokens + $completionTokens);
        
        // Deduct based on billing type
        try {
            if ($billingType === 'plan') {
                // Deduct from plan tokens
                $this->planSubscriptionService->deductPlanTokens($userId, $totalTokens);
                $this->planSubscriptionService->incrementDailyTokens($userId, $totalTokens);
                $cost = 0; // Plan tokens, no monetary cost
            } else {
                // Deduct from PAYG balance
                $this->creditService->deductForApiUsage($userId, $model, $promptTokens, $completionTokens, $priceMultiplier);
                $cost = $this->creditService->estimateCost($model, $promptTokens, $completionTokens, $priceMultiplier);
            }
        } catch (Exception $e) {
            // Log the error but don't fail the request since it was already processed
            error_log('Billing deduction failed: ' . $e->getMessage());
            $cost = 0;
        }
        
        // Log usage with detailed tracking
        $this->logUsage($apiKeyId, $userId, '/v1/chat/completions', $totalTokens, $cost, $promptTokens, $responseTimeMs, $model, $requestId, $completionTokens, $billingType);
        
        // Increment API key usage count
        $this->apiKeyModel->incrementUsage($apiKeyId);
        
        $this->json($response);
    }

    /**
     * Handle streaming chat completions
     */
    private function handleStreamingChatCompletions(
        array $body,
        string $model,
        int $apiKeyId,
        int $userId,
        float $priceMultiplier,
        int $estimatedInputTokens,
        float $startTime,
        string $requestId,
        string $billingType = 'payg'
    ): void {
        // Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_flush();
        }
        
        $outputTokens = 0;
        
        // Forward streaming request
        $this->proxyService->forwardStreamingRequest($body, $model, function($chunk) use (&$outputTokens) {
            // Count output tokens (rough estimate from chunk content)
            if (strpos($chunk, 'data: ') === 0) {
                $jsonData = substr($chunk, 6);
                if ($jsonData !== '[DONE]') {
                    $parsed = json_decode($jsonData, true);
                    if ($parsed && isset($parsed['choices'][0]['delta']['content'])) {
                        $content = $parsed['choices'][0]['delta']['content'];
                        $outputTokens += max(1, (int) ceil(mb_strlen($content) / 4));
                    }
                }
            }
            
            echo $chunk . "\n\n";
            flush();
        });
        
        // Calculate response time
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        
        // Calculate total tokens
        $totalTokens = $estimatedInputTokens + $outputTokens;
        
        // Deduct based on billing type
        try {
            if ($billingType === 'plan') {
                // Deduct from plan tokens
                $this->planSubscriptionService->deductPlanTokens($userId, $totalTokens);
                $this->planSubscriptionService->incrementDailyTokens($userId, $totalTokens);
                $cost = 0; // Plan tokens, no monetary cost
            } else {
                // Deduct from PAYG balance
                $this->creditService->deductForApiUsage($userId, $model, $estimatedInputTokens, $outputTokens, $priceMultiplier);
                $cost = $this->creditService->estimateCost($model, $estimatedInputTokens, $outputTokens, $priceMultiplier);
            }
        } catch (Exception $e) {
            error_log('Billing deduction failed: ' . $e->getMessage());
            $cost = 0;
        }
        
        // Log usage with detailed tracking
        $this->logUsage($apiKeyId, $userId, '/v1/chat/completions', $totalTokens, $cost, $estimatedInputTokens, $responseTimeMs, $model, $requestId, $outputTokens, $billingType);
        
        // Increment API key usage count
        $this->apiKeyModel->incrementUsage($apiKeyId);
        
        exit;
    }

    /**
     * Handle POST /v1/completions
     * OpenAI-compatible text completions endpoint
     */
    public function completions(): void
    {
        $startTime = microtime(true);
        $requestId = $this->generateRequestId();
        
        // Get authenticated key info
        $apiKeyId = (int) ($_SERVER['API_KEY_ID'] ?? 0);
        $userId = (int) ($_SERVER['API_USER_ID'] ?? 0);
        
        if (!$apiKeyId || !$userId) {
            $this->error('Unauthorized', 401, 'authentication_error');
        }
        
        // Parse request body
        $body = $this->getRequestBody();
        if (empty($body)) {
            $this->error('Invalid JSON in request body', 400);
        }
        
        // Extract parameters
        $model = $body['model'] ?? '';
        $prompt = $body['prompt'] ?? '';
        $stream = (bool) ($body['stream'] ?? false);
        
        // Validate model
        if (empty($model)) {
            $this->error('Model is required', 400);
        }
        
        if (!$this->proxyService->isValidModel($model)) {
            $this->error('Invalid model: ' . $model, 400, 'model_not_found');
        }
        
        // Validate prompt
        if (empty($prompt)) {
            $this->error('Prompt is required', 400);
        }
        
        // Convert prompt to messages format for upstream
        $messages = [
            ['role' => 'user', 'content' => is_array($prompt) ? implode("\n", $prompt) : $prompt]
        ];
        
        // Estimate input tokens
        $estimatedInputTokens = $this->tokenCounterService->countTokens(
            is_array($prompt) ? implode("\n", $prompt) : $prompt,
            $model
        );
        
        // Check daily token limit
        if (!$this->planService->checkDailyTokenLimit($userId, $estimatedInputTokens)) {
            $this->error('Daily token limit exceeded', 429, 'rate_limit_error');
        }
        
        // Check balance pre-flight
        $estimatedOutputTokens = 500;
        if (!$this->creditService->checkSufficientBalanceForRequest($userId, $model, $estimatedInputTokens, $estimatedOutputTokens)) {
            $this->error('Insufficient balance', 402, 'insufficient_balance');
        }
        
        // Get price multiplier
        $priceMultiplier = $this->planService->getPriceMultiplier($userId);
        
        // Build request for chat completions endpoint (most models use this)
        $chatBody = $body;
        unset($chatBody['prompt']);
        $chatBody['messages'] = $messages;
        
        if ($stream) {
            $this->handleStreamingCompletions($chatBody, $model, $apiKeyId, $userId, $priceMultiplier, $estimatedInputTokens, $startTime, $requestId);
        } else {
            $this->handleNonStreamingCompletions($chatBody, $model, $apiKeyId, $userId, $priceMultiplier, $startTime, $requestId);
        }
    }

    /**
     * Handle non-streaming completions
     */
    private function handleNonStreamingCompletions(
        array $body,
        string $model,
        int $apiKeyId,
        int $userId,
        float $priceMultiplier,
        float $startTime,
        string $requestId
    ): void {
        // Forward request
        $response = $this->proxyService->forwardRequest($body, $model, false);
        
        // Calculate response time
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        
        // Check for error
        if (isset($response['error'])) {
            $this->logUsage($apiKeyId, $userId, '/v1/completions', 0, 0, 0, $responseTimeMs, $model, $requestId);
            $this->json($response, $this->getErrorStatusCode($response['error']['type'] ?? 'api_error'));
        }
        
        // Extract usage
        $promptTokens = $response['usage']['prompt_tokens'] ?? 0;
        $completionTokens = $response['usage']['completion_tokens'] ?? 0;
        $totalTokens = $response['usage']['total_tokens'] ?? ($promptTokens + $completionTokens);
        
        // Deduct credits
        try {
            $this->creditService->deductForApiUsage($userId, $model, $promptTokens, $completionTokens, $priceMultiplier);
        } catch (Exception $e) {
            error_log('Credit deduction failed: ' . $e->getMessage());
        }
        
        // Calculate cost
        $cost = $this->creditService->estimateCost($model, $promptTokens, $completionTokens, $priceMultiplier);
        
        // Log usage with detailed tracking
        $this->logUsage($apiKeyId, $userId, '/v1/completions', $totalTokens, $cost, $promptTokens, $responseTimeMs, $model, $requestId, $completionTokens);
        
        // Increment usage
        $this->apiKeyModel->incrementUsage($apiKeyId);
        
        // Convert chat response back to completions format if needed
        $completionsResponse = $this->convertToCompletionsFormat($response);
        
        $this->json($completionsResponse);
    }

    /**
     * Handle streaming completions
     */
    private function handleStreamingCompletions(
        array $body,
        string $model,
        int $apiKeyId,
        int $userId,
        float $priceMultiplier,
        int $estimatedInputTokens,
        float $startTime,
        string $requestId
    ): void {
        // Set headers for SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('X-Accel-Buffering: no');
        
        if (ob_get_level()) {
            ob_end_flush();
        }
        
        $outputTokens = 0;
        
        // Forward streaming request
        $this->proxyService->forwardStreamingRequest($body, $model, function($chunk) use (&$outputTokens) {
            if (strpos($chunk, 'data: ') === 0) {
                $jsonData = substr($chunk, 6);
                if ($jsonData !== '[DONE]') {
                    $parsed = json_decode($jsonData, true);
                    if ($parsed && isset($parsed['choices'][0]['delta']['content'])) {
                        $content = $parsed['choices'][0]['delta']['content'];
                        $outputTokens += max(1, (int) ceil(mb_strlen($content) / 4));
                    }
                }
            }
            
            echo $chunk . "\n\n";
            flush();
        });
        
        // Calculate response time
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        
        // Deduct credits
        $totalTokens = $estimatedInputTokens + $outputTokens;
        try {
            $this->creditService->deductForApiUsage($userId, $model, $estimatedInputTokens, $outputTokens, $priceMultiplier);
        } catch (Exception $e) {
            error_log('Credit deduction failed: ' . $e->getMessage());
        }
        
        // Calculate cost
        $cost = $this->creditService->estimateCost($model, $estimatedInputTokens, $outputTokens, $priceMultiplier);
        
        // Log usage with detailed tracking
        $this->logUsage($apiKeyId, $userId, '/v1/completions', $totalTokens, $cost, $estimatedInputTokens, $responseTimeMs, $model, $requestId, $outputTokens);
        
        // Increment usage
        $this->apiKeyModel->incrementUsage($apiKeyId);
        
        exit;
    }

    /**
     * Convert chat completions response to completions format
     */
    private function convertToCompletionsFormat(array $chatResponse): array
    {
        // If already in completions format, return as-is
        if (isset($chatResponse['choices'][0]['text'])) {
            return $chatResponse;
        }
        
        // Convert from chat format
        $completionsResponse = [
            'id' => $chatResponse['id'] ?? 'cmpl-' . bin2hex(random_bytes(12)),
            'object' => 'text_completion',
            'created' => $chatResponse['created'] ?? time(),
            'model' => $chatResponse['model'] ?? '',
            'choices' => [],
            'usage' => $chatResponse['usage'] ?? [],
        ];
        
        if (isset($chatResponse['choices']) && is_array($chatResponse['choices'])) {
            foreach ($chatResponse['choices'] as $index => $choice) {
                $completionsResponse['choices'][] = [
                    'text' => $choice['message']['content'] ?? '',
                    'index' => $index,
                    'logprobs' => null,
                    'finish_reason' => $choice['finish_reason'] ?? 'stop',
                ];
            }
        }
        
        return $completionsResponse;
    }

    /**
     * Handle POST /v1/embeddings
     * OpenAI-compatible embeddings endpoint
     */
    public function embeddings(): void
    {
        $startTime = microtime(true);
        $requestId = $this->generateRequestId();
        
        // Get authenticated key info
        $apiKeyId = (int) ($_SERVER['API_KEY_ID'] ?? 0);
        $userId = (int) ($_SERVER['API_USER_ID'] ?? 0);
        
        if (!$apiKeyId || !$userId) {
            $this->error('Unauthorized', 401, 'authentication_error');
        }
        
        // Parse request body
        $body = $this->getRequestBody();
        if (empty($body)) {
            $this->error('Invalid JSON in request body', 400);
        }
        
        // Extract parameters
        $model = $body['model'] ?? '';
        $input = $body['input'] ?? '';
        
        // Validate model
        if (empty($model)) {
            $this->error('Model is required', 400);
        }
        
        if (!$this->proxyService->isValidModel($model)) {
            $this->error('Invalid model: ' . $model, 400, 'model_not_found');
        }
        
        // Validate input
        if (empty($input)) {
            $this->error('Input is required', 400);
        }
        
        // Calculate input tokens
        $inputText = is_array($input) ? implode(' ', $input) : $input;
        $inputTokens = $this->tokenCounterService->countTokens($inputText, $model);
        
        // Check daily token limit
        if (!$this->planService->checkDailyTokenLimit($userId, $inputTokens)) {
            $this->error('Daily token limit exceeded', 429, 'rate_limit_error');
        }
        
        // Check balance (embeddings have minimal output cost)
        if (!$this->creditService->checkSufficientBalanceForRequest($userId, $model, $inputTokens, 0)) {
            $this->error('Insufficient balance', 402, 'insufficient_balance');
        }
        
        // Get price multiplier
        $priceMultiplier = $this->planService->getPriceMultiplier($userId);
        
        // Forward request (embeddings use a different endpoint structure)
        // For simplicity, we'll use the same proxy method
        $response = $this->proxyService->forwardRequest($body, $model, false);
        
        // Calculate response time
        $responseTimeMs = (int) ((microtime(true) - $startTime) * 1000);
        
        // Check for error
        if (isset($response['error'])) {
            $this->logUsage($apiKeyId, $userId, '/v1/embeddings', 0, 0, 0, $responseTimeMs, $model, $requestId);
            $this->json($response, $this->getErrorStatusCode($response['error']['type'] ?? 'api_error'));
        }
        
        // Extract usage (embeddings only have prompt tokens)
        $promptTokens = $response['usage']['prompt_tokens'] ?? $inputTokens;
        $totalTokens = $response['usage']['total_tokens'] ?? $promptTokens;
        
        // Deduct credits (no output tokens for embeddings)
        try {
            $this->creditService->deductForApiUsage($userId, $model, $promptTokens, 0, $priceMultiplier);
        } catch (Exception $e) {
            error_log('Credit deduction failed: ' . $e->getMessage());
        }
        
        // Calculate cost
        $cost = $this->creditService->estimateCost($model, $promptTokens, 0, $priceMultiplier);
        
        // Log usage with detailed tracking
        $this->logUsage($apiKeyId, $userId, '/v1/embeddings', $totalTokens, $cost, $promptTokens, $responseTimeMs, $model, $requestId, 0);
        
        // Increment usage
        $this->apiKeyModel->incrementUsage($apiKeyId);
        
        $this->json($response);
    }

    /**
     * Handle GET /v1/models
     * Return list of available models in OpenAI format
     */
    public function listModels(): void
    {
        // Get authenticated key info (models endpoint may be publicly accessible)
        // but we still require authentication for consistency
        $apiKeyId = (int) ($_SERVER['API_KEY_ID'] ?? 0);
        $userId = (int) ($_SERVER['API_USER_ID'] ?? 0);
        
        if (!$apiKeyId || !$userId) {
            $this->error('Unauthorized', 401, 'authentication_error');
        }
        
        // Get mapped models from ProxyService
        $models = $this->proxyService->getMappedModels();
        $timestamp = time();
        
        // Build OpenAI-compatible response
        $data = [];
        foreach ($models as $modelId) {
            $data[] = [
                'id' => $modelId,
                'object' => 'model',
                'created' => $timestamp,
                'owned_by' => 'hay',
            ];
        }
        
        $this->json([
            'object' => 'list',
            'data' => $data,
        ]);
    }

    /**
     * Log API usage to database with detailed tracking
     */
    private function logUsage(
        int $apiKeyId,
        int $userId,
        string $endpoint,
        int $tokensUsed,
        float $cost,
        int $inputTokens = 0,
        int $responseTimeMs = 0,
        string $model = '',
        string $requestId = '',
        int $outputTokens = 0,
        string $billingType = 'payg'
    ): void {
        try {
            $this->usageLogModel->logApiRequest([
                'api_key_id' => $apiKeyId,
                'user_id' => $userId,
                'endpoint' => $endpoint,
                'tokens_used' => $tokensUsed,
                'input_tokens' => $inputTokens,
                'output_tokens' => $outputTokens,
                'model' => $model ?: null,
                'response_time_ms' => $responseTimeMs,
                'request_id' => $requestId ?: null,
                'cost' => $cost,
                'billing_type' => $billingType,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
                'response_code' => http_response_code(),
            ]);
        } catch (Exception $e) {
            error_log('Usage logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate a UUID-like request ID for tracking
     * 
     * @return string UUID v4 format string
     */
    private function generateRequestId(): string
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }

    /**
     * Get HTTP status code from error type
     */
    private function getErrorStatusCode(string $errorType): int
    {
        $statusCodes = [
            'invalid_request_error' => 400,
            'authentication_error' => 401,
            'insufficient_balance' => 402,
            'permission_error' => 403,
            'not_found' => 404,
            'model_not_found' => 404,
            'rate_limit_error' => 429,
            'server_error' => 500,
            'service_unavailable' => 503,
        ];
        
        return $statusCodes[$errorType] ?? 500;
    }
}
