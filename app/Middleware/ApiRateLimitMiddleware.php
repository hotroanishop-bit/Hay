<?php
/**
 * API Rate Limit Middleware
 * Checks user's plan rate limits for API requests
 * Returns 429 Too Many Requests when limits are exceeded
 */

class ApiRateLimitMiddleware
{
    private RateLimitService $rateLimitService;
    private Plan $planModel;

    public function __construct()
    {
        $this->rateLimitService = new RateLimitService();
        $this->planModel = new Plan();
    }

    /**
     * Handle the request
     * Checks rate limits based on user's plan
     *
     * @return bool Returns true if within limits, false if rate limited
     */
    public function handle(): bool
    {
        // Get user ID from API auth middleware (stored in $_SERVER)
        $userId = $_SERVER['API_USER_ID'] ?? null;

        if (!$userId) {
            // Should not happen if ApiAuthMiddleware runs first
            $this->tooManyRequests("Authentication required", 60);
            return false;
        }

        // Get user's plan
        $plan = $this->planModel->getUserPlan($userId);

        if (!$plan) {
            // No plan found, use very conservative default
            $plan = [
                'rate_limit_per_minute' => 10,
                'daily_token_limit' => 10000
            ];
        }

        // Check rate limit per minute
        $rateLimit = (int)($plan['rate_limit_per_minute'] ?? 60);
        $rateLimitKey = "api_rate:{$userId}";
        $window = 60; // 1 minute window

        // Check if user has exceeded rate limit
        if ($this->rateLimitService->isBlocked($rateLimitKey, $rateLimit, $window)) {
            $retryAfter = $this->rateLimitService->getResetTime($rateLimitKey, $window);
            $this->tooManyRequests(
                "Rate limit exceeded. Maximum {$rateLimit} requests per minute.",
                $retryAfter
            );
            return false;
        }

        // Increment the rate limit counter
        $this->rateLimitService->increment($rateLimitKey);

        // Store remaining requests in $_SERVER for potential use by controllers
        $remaining = $this->rateLimitService->getRemainingAttempts($rateLimitKey, $rateLimit, $window);
        $_SERVER['API_RATE_LIMIT'] = $rateLimit;
        $_SERVER['API_RATE_REMAINING'] = $remaining;

        return true;
    }

    /**
     * Send 429 Too Many Requests JSON response
     *
     * @param string $message Error message
     * @param int $retryAfter Seconds until rate limit resets
     */
    private function tooManyRequests(string $message, int $retryAfter): void
    {
        http_response_code(429);
        header("Retry-After: {$retryAfter}");
        echo json_encode([
            'error' => [
                'message' => $message,
                'type' => 'rate_limit_error',
                'code' => null
            ]
        ], JSON_UNESCAPED_UNICODE);
    }
}
