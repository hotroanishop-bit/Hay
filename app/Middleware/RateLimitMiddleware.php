<?php
/**
 * Rate Limit Middleware
 * Limits request rate using RateLimitService
 */

class RateLimitMiddleware
{
    private RateLimitService $rateLimitService;
    private int $limit;
    private int $window;
    private string $keyPrefix;

    /**
     * @param RateLimitService $rateLimitService
     * @param int $limit Maximum number of requests allowed (default: 60)
     * @param int $window Time window in seconds (default: 60)
     * @param string $keyPrefix Prefix for the rate limit key (default: 'request')
     */
    public function __construct(
        RateLimitService $rateLimitService,
        int $limit = 60,
        int $window = 60,
        string $keyPrefix = 'request'
    ) {
        $this->rateLimitService = $rateLimitService;
        $this->limit = $limit;
        $this->window = $window;
        $this->keyPrefix = $keyPrefix;
    }

    /**
     * Handle the request
     * Checks rate limit and returns 429 if exceeded
     *
     * @param callable|null $next The next handler in the chain
     * @return bool|mixed Returns false if rate limited, or result of next handler
     */
    public function handle(?callable $next = null): mixed
    {
        $key = $this->getClientKey();

        // Check if request is within rate limit
        if ($this->rateLimitService->isBlocked($key, $this->limit, $this->window)) {
            $this->sendRateLimitExceeded($key);
            return false;
        }

        // Increment the counter
        $this->rateLimitService->increment($key);

        // Set rate limit headers
        $this->setRateLimitHeaders($key);

        // Continue to next handler
        if ($next !== null) {
            return $next();
        }

        return true;
    }

    /**
     * Get the rate limit key for the current client
     */
    private function getClientKey(): string
    {
        // Use IP address as identifier
        $ip = $this->getClientIp();

        // Include user ID if authenticated
        $userId = $_SESSION['user_id'] ?? 'guest';

        return $this->keyPrefix . '_' . $ip . '_' . $userId;
    }

    /**
     * Get the client IP address
     */
    private function getClientIp(): string
    {
        // Check for forwarded IP (behind proxy/load balancer)
        $forwardedFor = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null;
        if ($forwardedFor) {
            // Take the first IP in the list
            $ips = explode(',', $forwardedFor);
            return trim($ips[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Set rate limit response headers
     */
    private function setRateLimitHeaders(string $key): void
    {
        $remaining = $this->rateLimitService->getRemainingAttempts($key, $this->limit, $this->window);
        $resetTime = time() + $this->window;

        header('X-RateLimit-Limit: ' . $this->limit);
        header('X-RateLimit-Remaining: ' . max(0, $remaining - 1));
        header('X-RateLimit-Reset: ' . $resetTime);
    }

    /**
     * Send 429 Too Many Requests response
     */
    private function sendRateLimitExceeded(string $key): void
    {
        $resetTime = $this->rateLimitService->getResetTime($key, $this->window);

        http_response_code(429);
        header('Content-Type: application/json; charset=UTF-8');
        header('X-RateLimit-Limit: ' . $this->limit);
        header('X-RateLimit-Remaining: 0');
        header('X-RateLimit-Reset: ' . (time() + $resetTime));
        header('Retry-After: ' . $resetTime);

        echo json_encode([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $resetTime
        ]);
        exit;
    }
}
