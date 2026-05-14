<?php
/**
 * Plan Subscription Service
 * Handles dual billing (PAYG + Plan) subscription logic
 */

class PlanSubscriptionService
{
    private User $userModel;
    private Plan $planModel;
    private UserPlan $userPlanModel;

    public function __construct(User $userModel, Plan $planModel, UserPlan $userPlanModel)
    {
        $this->userModel = $userModel;
        $this->planModel = $planModel;
        $this->userPlanModel = $userPlanModel;
    }

    /**
     * Check if user has sufficient plan tokens
     */
    public function hasSufficientPlanTokens(int $userId, int $tokens): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $planTokens = (int) ($user['plan_tokens'] ?? 0);
        
        // Check if plan tokens have expired
        $expiresAt = $user['plan_tokens_expires_at'] ?? null;
        if ($expiresAt !== null && strtotime($expiresAt) < time()) {
            return false;
        }
        
        return $planTokens >= $tokens;
    }

    /**
     * Check if user has sufficient PAYG balance
     */
    public function hasSufficientPaygBalance(int $userId, float $amount): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $paygBalance = (float) ($user['payg_balance'] ?? 0);
        return $paygBalance >= $amount;
    }

    /**
     * Check if user is within daily token limit (for free plan users)
     */
    public function checkDailyTokenLimit(int $userId, int $tokens): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Get user's current plan
        $planId = $user['current_plan_id'] ?? null;
        if (!$planId) {
            // No plan, check global daily limit (assume 10000 tokens for free users)
            $dailyLimit = 10000;
        } else {
            $plan = $this->planModel->find($planId);
            if (!$plan) {
                return true; // No plan restrictions
            }
            
            // Only apply daily limit for free plans
            if (!($plan['is_free'] ?? false)) {
                return true; // Paid plans have no daily limit
            }
            
            $dailyLimit = (int) ($plan['daily_token_limit'] ?? 0);
            if ($dailyLimit === 0) {
                return true; // No limit set
            }
        }
        
        $dailyUsed = (int) ($user['daily_tokens_used'] ?? 0);
        
        return ($dailyUsed + $tokens) <= $dailyLimit;
    }

    /**
     * Increment daily tokens used
     */
    public function incrementDailyTokens(int $userId, int $tokens): bool
    {
        return $this->userModel->execute(
            "UPDATE users SET daily_tokens_used = daily_tokens_used + :tokens, updated_at = NOW() WHERE id = :user_id",
            ['tokens' => $tokens, 'user_id' => $userId]
        );
    }

    /**
     * Get user's preferred billing type, or switch to alternative if primary is insufficient
     */
    public function getPreferredBillingType(int $userId): string
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return 'payg';
        }
        
        return $user['preferred_billing_type'] ?? 'payg';
    }

    /**
     * Update user's preferred billing type
     */
    public function updatePreferredBillingType(int $userId, string $billingType): bool
    {
        $validTypes = ['payg', 'plan'];
        if (!in_array($billingType, $validTypes)) {
            return false;
        }
        
        return $this->userModel->execute(
            "UPDATE users SET preferred_billing_type = :billing_type, updated_at = NOW() WHERE id = :user_id",
            ['billing_type' => $billingType, 'user_id' => $userId]
        );
    }

    /**
     * Get both balances for a user
     */
    public function getBothBalances(int $userId): array
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return [
                'payg_balance' => 0.0,
                'plan_tokens' => 0,
                'plan_tokens_expires_at' => null,
                'daily_tokens_used' => 0,
                'daily_tokens_reset_at' => null,
                'preferred_billing_type' => 'payg',
                'current_plan_id' => null
            ];
        }
        
        return [
            'payg_balance' => (float) ($user['payg_balance'] ?? 0),
            'plan_tokens' => (int) ($user['plan_tokens'] ?? 0),
            'plan_tokens_expires_at' => $user['plan_tokens_expires_at'] ?? null,
            'daily_tokens_used' => (int) ($user['daily_tokens_used'] ?? 0),
            'daily_tokens_reset_at' => $user['daily_tokens_reset_at'] ?? null,
            'preferred_billing_type' => $user['preferred_billing_type'] ?? 'payg',
            'current_plan_id' => $user['current_plan_id'] ?? null
        ];
    }

    /**
     * Cancel user's current plan subscription
     */
    public function cancelPlan(int $userId): bool
    {
        // Cancel in user_plans table
        $this->userPlanModel->cancel($userId);
        
        // Clear plan info from users table
        return $this->userModel->execute(
            "UPDATE users SET current_plan_id = NULL, plan_tokens = 0, plan_tokens_expires_at = NULL, updated_at = NOW() WHERE id = :user_id",
            ['user_id' => $userId]
        );
    }

    /**
     * Get available plans for subscription
     */
    public function getAvailablePlans(): array
    {
        return $this->planModel->findActive();
    }

    /**
     * Subscribe a user to a plan
     */
    public function subscribeToPlan(int $userId, int $planId): int
    {
        $plan = $this->planModel->find($planId);
        
        if (!$plan) {
            throw new Exception('Plan not found');
        }
        
        $tokenQuota = (int) ($plan['token_quota'] ?? 0);
        $durationDays = (int) ($plan['duration_days'] ?? 30);
        
        // Calculate expiration date
        $expiresAt = $durationDays > 0 
            ? date('Y-m-d H:i:s', strtotime("+{$durationDays} days"))
            : null;
        
        // Create user plan subscription
        $subscriptionId = $this->userPlanModel->subscribe($userId, $planId, $tokenQuota, $expiresAt);
        
        // Update user's current plan
        $this->userModel->execute(
            "UPDATE users SET current_plan_id = :plan_id, plan_tokens = :tokens, 
             plan_tokens_expires_at = :expires_at, updated_at = NOW() WHERE id = :user_id",
            [
                'plan_id' => $planId,
                'tokens' => $tokenQuota,
                'expires_at' => $expiresAt,
                'user_id' => $userId
            ]
        );
        
        return $subscriptionId;
    }

    /**
     * Check and reset daily tokens if a new day has started
     */
    public function checkAndResetDailyTokens(int $userId): bool
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        $today = date('Y-m-d');
        $lastReset = $user['daily_tokens_reset_at'] ?? null;
        
        if ($lastReset !== $today) {
            // New day - reset daily tokens
            $this->userModel->execute(
                "UPDATE users SET daily_tokens_used = 0, daily_tokens_reset_at = :today, updated_at = NOW() WHERE id = :user_id",
                ['today' => $today, 'user_id' => $userId]
            );
            return true;
        }
        
        return false;
    }

    /**
     * Deduct tokens from user's plan balance
     */
    public function deductPlanTokens(int $userId, int $tokens): bool
    {
        // Update user's plan_tokens
        $result = $this->userModel->execute(
            "UPDATE users SET plan_tokens = plan_tokens - :tokens, 
             daily_tokens_used = daily_tokens_used + :tokens2, 
             updated_at = NOW() 
             WHERE id = :user_id AND plan_tokens >= :tokens3",
            [
                'tokens' => $tokens,
                'tokens2' => $tokens,
                'user_id' => $userId,
                'tokens3' => $tokens
            ]
        );
        
        // Also update the user_plans table
        $this->userPlanModel->deductTokens($userId, $tokens);
        
        return $result;
    }

    /**
     * Deduct from user's PAYG balance
     */
    public function deductPaygBalance(int $userId, float $amount): bool
    {
        return $this->userModel->execute(
            "UPDATE users SET payg_balance = payg_balance - :amount, updated_at = NOW() 
             WHERE id = :user_id AND payg_balance >= :amount2",
            [
                'amount' => $amount,
                'user_id' => $userId,
                'amount2' => $amount
            ]
        );
    }

    /**
     * Add to user's PAYG balance
     */
    public function addPaygBalance(int $userId, float $amount): bool
    {
        return $this->userModel->execute(
            "UPDATE users SET payg_balance = payg_balance + :amount, updated_at = NOW() WHERE id = :user_id",
            ['amount' => $amount, 'user_id' => $userId]
        );
    }

    /**
     * Determine billing type from request header or user preference
     */
    public function getBillingType(int $userId, ?string $requestHeader = null): string
    {
        // If request header specifies billing type, use that
        if ($requestHeader !== null) {
            $headerLower = strtolower(trim($requestHeader));
            if (in_array($headerLower, ['payg', 'plan'])) {
                return $headerLower;
            }
        }
        
        // Otherwise, use user's preference
        $user = $this->userModel->find($userId);
        
        if ($user && isset($user['preferred_billing_type'])) {
            return $user['preferred_billing_type'];
        }
        
        return 'payg'; // Default to PAYG
    }

    /**
     * Get effective balance based on billing type
     */
    public function getEffectiveBalance(int $userId, string $billingType): array
    {
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return [
                'type' => $billingType,
                'balance' => 0,
                'unit' => $billingType === 'plan' ? 'tokens' : 'credits'
            ];
        }
        
        if ($billingType === 'plan') {
            return [
                'type' => 'plan',
                'balance' => (int) ($user['plan_tokens'] ?? 0),
                'unit' => 'tokens',
                'daily_used' => (int) ($user['daily_tokens_used'] ?? 0),
                'expires_at' => $user['plan_tokens_expires_at'] ?? null
            ];
        }
        
        return [
            'type' => 'payg',
            'balance' => (float) ($user['payg_balance'] ?? 0),
            'unit' => 'credits'
        ];
    }

    /**
     * Check if user has sufficient balance for a request
     */
    public function hasSufficientBalance(int $userId, string $billingType, float $amount): bool
    {
        $balance = $this->getEffectiveBalance($userId, $billingType);
        return $balance['balance'] >= $amount;
    }

    /**
     * Get current plan details for a user
     */
    public function getCurrentPlanDetails(int $userId): ?array
    {
        $userPlan = $this->userPlanModel->getCurrentPlan($userId);
        
        if (!$userPlan) {
            return null;
        }
        
        $plan = $this->planModel->find($userPlan['plan_id']);
        
        return [
            'subscription' => $userPlan,
            'plan' => $plan
        ];
    }
}
