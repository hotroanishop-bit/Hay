<?php
/**
 * Proxy Service
 * Handles forwarding requests to upstream AI providers through squid proxy
 * with model mapping, retry logic, and response sanitization
 * 
 * All internal details (real model names, proxy info, upstream URLs) are hidden from users
 */

class ProxyService
{
    private array $config;
    private array $upstreamConfig;
    private array $proxyConfig;
    private array $modelMapping;
    private array $retryConfig;
    private array $timeoutConfig;
    private array $retriableStatusCodes;

    public function __construct()
    {
        $this->config = require __DIR__ . '/../../config/proxy.php';
        $this->upstreamConfig = $this->config['upstream'];
        $this->proxyConfig = $this->config['proxy'];
        $this->modelMapping = $this->config['model_mapping'];
        $this->retryConfig = $this->config['retry'];
        $this->timeoutConfig = $this->config['timeout'] ?? [
            'connect' => 10,
            'request' => 120,
            'streaming' => 300,
        ];
        $this->retriableStatusCodes = $this->config['retriable_status_codes'] ?? [500, 502, 503, 504, 429];
    }

    /**
     * Map a fake model name to the real model name
     * 
     * @param string $fakeModel The user-facing model name
     * @return string The real model name for upstream
     * @throws InvalidArgumentException If model is not mapped
     */
    public function mapModel(string $fakeModel): string
    {
        if (!isset($this->modelMapping[$fakeModel])) {
            throw new InvalidArgumentException('Invalid model: ' . $fakeModel);
        }
        return $this->modelMapping[$fakeModel];
    }

    /**
     * Get list of available fake model names that users can request
     * 
     * @return array List of fake model names
     */
    public function getMappedModels(): array
    {
        return array_keys($this->modelMapping);
    }

    /**
     * Check if a model name is valid (mapped)
     * 
     * @param string $fakeModel The user-facing model name
     * @return bool True if model is valid
     */
    public function isValidModel(string $fakeModel): bool
    {
        return isset($this->modelMapping[$fakeModel]);
    }

    /**
     * Build cURL options for upstream request
     * 
     * @param array $requestData The request payload
     * @param string $realModel The real model name to use
     * @param bool $stream Whether this is a streaming request
     * @return array cURL options array
     */
    public function buildCurlOptions(array $requestData, string $realModel, bool $stream = false): array
    {
        // Replace model in request data with real model
        $requestData['model'] = $realModel;
        
        $options = [
            CURLOPT_URL => $this->upstreamConfig['base_url'] . '/chat/completions',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($requestData),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $this->upstreamConfig['api_key'],
                'Accept: application/json',
            ],
            // Proxy configuration
            CURLOPT_PROXY => $this->proxyConfig['host'] . ':' . $this->proxyConfig['port'],
            CURLOPT_PROXYUSERPWD => $this->proxyConfig['username'] . ':' . $this->proxyConfig['password'],
            CURLOPT_PROXYTYPE => CURLPROXY_HTTP,
            // Timeout settings
            CURLOPT_CONNECTTIMEOUT => $this->timeoutConfig['connect'],
            CURLOPT_TIMEOUT => $stream ? $this->timeoutConfig['streaming'] : $this->timeoutConfig['request'],
            // SSL settings
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            // Follow redirects
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 3,
        ];

        if ($stream) {
            // For streaming, we handle the response differently
            $options[CURLOPT_HTTPHEADER][] = 'Accept: text/event-stream';
        }

        return $options;
    }

    /**
     * Forward a non-streaming request to upstream
     * 
     * @param array $requestData The original request data
     * @param string $fakeModel The user-facing model name
     * @param bool $stream Whether to enable streaming (for future use)
     * @return array The sanitized response
     * @throws Exception On unrecoverable error
     */
    public function forwardRequest(array $requestData, string $fakeModel, bool $stream = false): array
    {
        // Map fake model to real model
        $realModel = $this->mapModel($fakeModel);
        
        // Build cURL options
        $curlOptions = $this->buildCurlOptions($requestData, $realModel, $stream);
        
        $lastError = null;
        $lastStatusCode = 0;
        $lastResponseBody = '';
        
        // Retry loop
        for ($attempt = 1; $attempt <= $this->retryConfig['max_attempts']; $attempt++) {
            $ch = curl_init();
            curl_setopt_array($ch, $curlOptions);
            
            $responseBody = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            // Check for cURL errors
            if ($curlErrno !== 0) {
                $lastError = $curlError;
                $lastStatusCode = 0;
                $lastResponseBody = '';
                
                // Check if we should retry
                $errorResult = $this->handleUpstreamError(0, '', $attempt);
                if (!empty($errorResult['retry']) && $attempt < $this->retryConfig['max_attempts']) {
                    usleep($this->retryConfig['delay_ms'] * 1000);
                    continue;
                }
                
                return $this->createErrorResponse('service_unavailable', 'Service temporarily unavailable');
            }
            
            $lastStatusCode = $statusCode;
            $lastResponseBody = $responseBody;
            
            // Successful response
            if ($statusCode >= 200 && $statusCode < 300) {
                $response = json_decode($responseBody, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return $this->createErrorResponse('invalid_response', 'Invalid response from server');
                }
                return $this->sanitizeResponse($response, $fakeModel);
            }
            
            // Check if we should retry this error
            $errorResult = $this->handleUpstreamError($statusCode, $responseBody, $attempt);
            if (!empty($errorResult['retry']) && $attempt < $this->retryConfig['max_attempts']) {
                usleep($this->retryConfig['delay_ms'] * 1000);
                continue;
            }
            
            // Return sanitized error
            return $errorResult;
        }
        
        // All retries exhausted
        return $this->handleUpstreamError($lastStatusCode, $lastResponseBody, $this->retryConfig['max_attempts']);
    }

    /**
     * Forward a streaming request to upstream
     * 
     * @param array $requestData The original request data
     * @param string $fakeModel The user-facing model name
     * @param callable $onChunk Callback for each SSE chunk: function(string $data): void
     * @throws Exception On unrecoverable error
     */
    public function forwardStreamingRequest(array $requestData, string $fakeModel, callable $onChunk): void
    {
        // Map fake model to real model
        $realModel = $this->mapModel($fakeModel);
        
        // Ensure stream is enabled
        $requestData['stream'] = true;
        
        // Build cURL options
        $curlOptions = $this->buildCurlOptions($requestData, $realModel, true);
        
        // Buffer for incomplete SSE data
        $buffer = '';
        
        // Custom write function for streaming
        $curlOptions[CURLOPT_WRITEFUNCTION] = function($ch, $data) use (&$buffer, $fakeModel, $realModel, $onChunk) {
            $buffer .= $data;
            
            // Process complete SSE lines
            while (($pos = strpos($buffer, "\n")) !== false) {
                $line = substr($buffer, 0, $pos);
                $buffer = substr($buffer, $pos + 1);
                
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                
                // Handle SSE data lines
                if (strpos($line, 'data: ') === 0) {
                    $jsonData = substr($line, 6);
                    
                    // Check for stream end
                    if ($jsonData === '[DONE]') {
                        $onChunk('data: [DONE]');
                        continue;
                    }
                    
                    // Parse and sanitize JSON
                    $parsed = json_decode($jsonData, true);
                    if ($parsed !== null) {
                        $sanitized = $this->sanitizeStreamChunk($parsed, $fakeModel, $realModel);
                        $onChunk('data: ' . json_encode($sanitized));
                    } else {
                        // Pass through non-JSON data as-is (shouldn't happen normally)
                        $onChunk($line);
                    }
                }
            }
            
            return strlen($data);
        };
        
        $lastError = null;
        
        // Retry loop for streaming
        for ($attempt = 1; $attempt <= $this->retryConfig['max_attempts']; $attempt++) {
            $buffer = ''; // Reset buffer for each attempt
            
            $ch = curl_init();
            curl_setopt_array($ch, $curlOptions);
            
            curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            curl_close($ch);
            
            // Successful streaming (status code checked before streaming starts)
            if ($curlErrno === 0 && $statusCode >= 200 && $statusCode < 300) {
                return;
            }
            
            // Check if we should retry
            if ($curlErrno !== 0) {
                $lastError = $curlError;
                $errorResult = $this->handleUpstreamError(0, '', $attempt);
            } else {
                $errorResult = $this->handleUpstreamError($statusCode, '', $attempt);
            }
            
            if (!empty($errorResult['retry']) && $attempt < $this->retryConfig['max_attempts']) {
                usleep($this->retryConfig['delay_ms'] * 1000);
                continue;
            }
            
            // Send error as SSE event
            $onChunk('data: ' . json_encode($errorResult));
            $onChunk('data: [DONE]');
            return;
        }
        
        // All retries exhausted - send error
        $errorResponse = $this->createErrorResponse('service_unavailable', 'Service temporarily unavailable');
        $onChunk('data: ' . json_encode($errorResponse));
        $onChunk('data: [DONE]');
    }

    /**
     * Sanitize response by replacing real model names with fake model names
     * and removing any upstream provider information
     * 
     * @param array $response The raw response from upstream
     * @param string $fakeModel The fake model name to use
     * @return array Sanitized response
     */
    public function sanitizeResponse(array $response, string $fakeModel): array
    {
        // Replace model name
        if (isset($response['model'])) {
            $response['model'] = $fakeModel;
        }
        
        // Remove any system_fingerprint that might reveal provider info
        unset($response['system_fingerprint']);
        
        // Sanitize choices if present
        if (isset($response['choices']) && is_array($response['choices'])) {
            foreach ($response['choices'] as &$choice) {
                // Remove any internal references
                unset($choice['logprobs']);
            }
        }
        
        // Remove any provider-specific fields that might leak info
        $fieldsToRemove = [
            'x-request-id',
            'x-ratelimit-limit-requests',
            'x-ratelimit-limit-tokens',
            'x-ratelimit-remaining-requests',
            'x-ratelimit-remaining-tokens',
            'x-ratelimit-reset-requests',
            'x-ratelimit-reset-tokens',
            'organization',
            'service_tier',
        ];
        
        foreach ($fieldsToRemove as $field) {
            unset($response[$field]);
        }
        
        return $response;
    }

    /**
     * Sanitize a streaming chunk
     * 
     * @param array $chunk The parsed chunk data
     * @param string $fakeModel The fake model name
     * @param string $realModel The real model name to replace
     * @return array Sanitized chunk
     */
    private function sanitizeStreamChunk(array $chunk, string $fakeModel, string $realModel): array
    {
        // Replace model name
        if (isset($chunk['model'])) {
            $chunk['model'] = $fakeModel;
        }
        
        // Remove system fingerprint
        unset($chunk['system_fingerprint']);
        
        return $chunk;
    }

    /**
     * Parse completion response and extract usage statistics
     * 
     * @param string $jsonResponse Raw JSON response string
     * @param string $fakeModel The fake model name for sanitization
     * @return array Parsed and sanitized response with usage stats
     */
    public function parseCompletionResponse(string $jsonResponse, string $fakeModel): array
    {
        $response = json_decode($jsonResponse, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->createErrorResponse('invalid_response', 'Invalid response format');
        }
        
        // Sanitize the response
        $sanitized = $this->sanitizeResponse($response, $fakeModel);
        
        // Extract usage if present
        $usage = [
            'prompt_tokens' => $response['usage']['prompt_tokens'] ?? 0,
            'completion_tokens' => $response['usage']['completion_tokens'] ?? 0,
            'total_tokens' => $response['usage']['total_tokens'] ?? 0,
        ];
        
        return [
            'response' => $sanitized,
            'usage' => $usage,
        ];
    }

    /**
     * Handle upstream errors with retry logic and sanitization
     * 
     * @param int $statusCode HTTP status code (0 for connection errors)
     * @param string $responseBody Raw response body
     * @param int $attempt Current attempt number
     * @return array Error response or retry indicator
     */
    public function handleUpstreamError(int $statusCode, string $responseBody, int $attempt): array
    {
        // Check if error is retriable
        $isRetriable = $statusCode === 0 || in_array($statusCode, $this->retriableStatusCodes);
        
        // Return retry indicator if we should retry
        if ($isRetriable && $attempt < $this->retryConfig['max_attempts']) {
            return ['retry' => true];
        }
        
        // Map status codes to user-friendly error messages
        // Hide all internal details
        switch ($statusCode) {
            case 0:
                return $this->createErrorResponse(
                    'service_unavailable',
                    'Service temporarily unavailable. Please try again later.',
                    503
                );
            
            case 400:
                return $this->createErrorResponse(
                    'invalid_request_error',
                    'Invalid request. Please check your input.',
                    400
                );
            
            case 401:
                return $this->createErrorResponse(
                    'authentication_error',
                    'Authentication failed. Please contact support.',
                    401
                );
            
            case 403:
                return $this->createErrorResponse(
                    'permission_error',
                    'Access denied. Please contact support.',
                    403
                );
            
            case 404:
                return $this->createErrorResponse(
                    'not_found',
                    'The requested resource was not found.',
                    404
                );
            
            case 429:
                return $this->createErrorResponse(
                    'rate_limit_error',
                    'Rate limit exceeded. Please slow down your requests.',
                    429
                );
            
            case 500:
            case 502:
            case 503:
            case 504:
                return $this->createErrorResponse(
                    'server_error',
                    'Server error. Please try again later.',
                    $statusCode
                );
            
            default:
                return $this->createErrorResponse(
                    'api_error',
                    'An error occurred. Please try again.',
                    $statusCode ?: 500
                );
        }
    }

    /**
     * Create an OpenAI-compatible error response
     * 
     * @param string $type Error type
     * @param string $message User-friendly error message
     * @param int $statusCode HTTP status code (for reference)
     * @return array Error response in OpenAI format
     */
    private function createErrorResponse(string $type, string $message, int $statusCode = 500): array
    {
        return [
            'error' => [
                'message' => $message,
                'type' => $type,
                'param' => null,
                'code' => $type,
            ],
        ];
    }

    /**
     * Get the configured upstream base URL (for internal use only)
     * 
     * @return string
     */
    public function getUpstreamBaseUrl(): string
    {
        return $this->upstreamConfig['base_url'];
    }

    /**
     * Get retry configuration
     * 
     * @return array
     */
    public function getRetryConfig(): array
    {
        return $this->retryConfig;
    }
}
