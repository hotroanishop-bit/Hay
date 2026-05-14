<?php
/**
 * Plan Service
 * Handles plan-related operations including rate limits, token limits, and pricing
 */

class PlanService
{
    private Plan $planModel;
    private UsageLog $usageLogModel;
    private RateLimitService $rateLimitService;

    public function __construct(Plan $planModel, UsageLog $usageLogModel)
    {
        $this->planModel = $planModel;
        $this->usageLogModel = $usageLogModel;
        $this->rateLimitService = new RateLimitService();
    }

    /**
     * Get the user's current plan or default Basic plan
     * 
     * @param int $userId The user ID
     * @return array|null The plan data or null if not found
     */
    public function getUserPlan(int $userId): ?array
    {
        return $this->planModel->getUserPlan($userId);
    }

    /**
     * Check if user is within their per-minute rate limit
     * This is a double-check utility; primary rate limiting should be in middleware
     * 
     * @param int $userId The user ID
     * @return bool True if within rate limit, false if exceeded
     */
    public function checkRateLimit(int $userId): bool
    {
        $plan = $this->getUserPlan($userId);
        
        if (!$plan) {
            return false;
        }

        $rateLimit = (int) ($plan['rate_limit_per_minute'] ?? 60);
        $key = "api_rate:{$userId}";
        $window = 60; // 1 minute window

        return $this->rateLimitService->check($key, $rateLimit, $window);
    }

    /**
     * Check if user is within their daily token limit
     * 
     * @param int $userId The user ID
     * @param int $estimatedTokens The estimated tokens for the request
     * @return bool True if within limit, false if would exceed
     */
    public function checkDailyTokenLimit(int $userId, int $estimatedTokens): bool
    {
        $plan = $this->getUserPlan($userId);
        
        if (!$plan) {
            return false;
        }

        $dailyLimit = $plan['daily_token_limit'];
        
        // NULL means unlimited
        if ($dailyLimit === null) {
            return true;
        }

        $dailyLimit = (int) $dailyLimit;
        $usedToday = $this->getTodayTokenUsage($userId);
        
        return ($usedToday + $estimatedTokens) <= $dailyLimit;
    }

    /**
     * Get the price multiplier for a user's plan
     * 
     * @param int $userId The user ID
     * @return float The price multiplier (default 1.0)
     */
    public function getPriceMultiplier(int $userId): float
    {
        $plan = $this->getUserPlan($userId);
        
        if (!$plan) {
            return 1.0;
        }

        return (float) ($plan['price_multiplier'] ?? 1.0);
    }

    /**
     * Get remaining daily tokens for a user
     * 
     * @param int $userId The user ID
     * @return int|null Remaining tokens, or null for unlimited
     */
    public function getRemainingDailyTokens(int $userId): ?int
    {
        $plan = $this->getUserPlan($userId);
        
        if (!$plan) {
            return 0;
        }

        $dailyLimit = $plan['daily_token_limit'];
        
        // NULL means unlimited
        if ($dailyLimit === null) {
            return null;
        }

        $dailyLimit = (int) $dailyLimit;
        $usedToday = $this->getTodayTokenUsage($userId);
        
        return max(0, $dailyLimit - $usedToday);
    }

    /**
     * Get total tokens used today by a user
     * 
     * @param int $userId The user ID
     * @return int Total tokens used today
     */
    private function getTodayTokenUsage(int $userId): int
    {
        $startOfDay = date('Y-m-d 00:00:00');
        $endOfDay = date('Y-m-d 23:59:59');
        
        $stats = $this->usageLogModel->getStatsByUser($userId, $startOfDay, $endOfDay);
        
        return (int) ($stats['total_tokens'] ?? 0);
    }
}
