<?php
/**
 * API Auth Middleware
 * Validates Bearer token from Authorization header for API requests
 * Stateless authentication using API keys
 */

class ApiAuthMiddleware
{
    private ApiKey $apiKeyModel;

    public function __construct()
    {
        $this->apiKeyModel = new ApiKey();
    }

    /**
     * Handle the request
     * Extracts and validates Bearer token from Authorization header
     *
     * @return bool Returns true if authenticated, false otherwise
     */
    public function handle(): bool
    {
        // Get Authorization header
        $authHeader = $this->getAuthorizationHeader();
        
        if (empty($authHeader)) {
            $this->unauthorized("Missing Authorization header");
            return false;
        }

        // Check for Bearer token format
        if (!preg_match('/^Bearer\s+(.+)$/i', $authHeader, $matches)) {
            $this->unauthorized("Invalid Authorization header format. Expected: Bearer <token>");
            return false;
        }

        $token = $matches[1];

        // Validate token format (should start with hay_)
        if (strpos($token, 'hay_') !== 0) {
            $this->unauthorized("Invalid API key format");
            return false;
        }

        // Find the API key by plain key
        $apiKey = $this->apiKeyModel->findByPlainKey($token);

        if (!$apiKey) {
            $this->unauthorized("Invalid API key");
            return false;
        }

        // Check if key is valid (active and not expired)
        if (!$this->apiKeyModel->isValid($apiKey['id'])) {
            $this->unauthorized("API key is inactive or expired");
            return false;
        }

        // Store validated key data in $_SERVER for use by controllers
        $_SERVER['API_KEY'] = $apiKey;
        $_SERVER['API_KEY_ID'] = $apiKey['id'];
        $_SERVER['API_USER_ID'] = $apiKey['user_id'];

        return true;
    }

    /**
     * Get Authorization header from request
     * Handles various server configurations
     *
     * @return string|null Authorization header value or null
     */
    private function getAuthorizationHeader(): ?string
    {
        // Try standard header
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        // Try Apache-specific
        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        // Try getallheaders() function (Apache)
        if (function_exists('getallheaders')) {
            $headers = getallheaders();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
            // Case-insensitive check
            foreach ($headers as $key => $value) {
                if (strtolower($key) === 'authorization') {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Send 401 Unauthorized JSON response
     *
     * @param string $message Error message
     */
    private function unauthorized(string $message): void
    {
        http_response_code(401);
        header('WWW-Authenticate: Bearer');
        echo json_encode([
            'error' => [
                'message' => $message,
                'type' => 'authentication_error',
                'code' => null
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}
