<?php
/**
 * UserPlan Model
 * Handles user plan subscriptions with token tracking
 */

class UserPlan extends BaseModel
{
    protected string $table = 'user_plans';
    
    protected array $fillable = [
        'user_id',
        'plan_id',
        'tokens_remaining',
        'started_at',
        'expires_at',
        'status',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the current active plan for a user
     */
    public function getCurrentPlan(int $userId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id AND status = 'active' 
                ORDER BY created_at DESC 
                LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Subscribe a user to a plan
     */
    public function subscribe(int $userId, int $planId, int $tokensRemaining = 0, ?string $expiresAt = null): int
    {
        // Cancel any existing active plans for this user
        $this->cancel($userId);
        
        return $this->create([
            'user_id' => $userId,
            'plan_id' => $planId,
            'tokens_remaining' => $tokensRemaining,
            'started_at' => date('Y-m-d H:i:s'),
            'expires_at' => $expiresAt,
            'status' => 'active',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Cancel a user's active plan subscription
     */
    public function cancel(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET status = 'cancelled', updated_at = NOW() 
                WHERE user_id = :user_id AND status = 'active'";
        return $this->execute($sql, ['user_id' => $userId]);
    }

    /**
     * Check if a plan subscription is expired
     */
    public function isExpired(int $id): bool
    {
        $userPlan = $this->find($id);
        
        if (!$userPlan) {
            return true;
        }
        
        if ($userPlan['status'] !== 'active') {
            return true;
        }
        
        if ($userPlan['expires_at'] === null) {
            return false; // No expiration date means not expired
        }
        
        return strtotime($userPlan['expires_at']) < time();
    }

    /**
     * Get remaining tokens for a user's active plan
     */
    public function getRemainingTokens(int $userId): int
    {
        $currentPlan = $this->getCurrentPlan($userId);
        
        if (!$currentPlan) {
            return 0;
        }
        
        return (int) ($currentPlan['tokens_remaining'] ?? 0);
    }

    /**
     * Update tokens remaining for a subscription
     */
    public function updateTokens(int $id, int $tokensRemaining): bool
    {
        $sql = "UPDATE {$this->table} SET tokens_remaining = :tokens, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['tokens' => $tokensRemaining, 'id' => $id]);
    }

    /**
     * Deduct tokens from active plan
     */
    public function deductTokens(int $userId, int $tokens): bool
    {
        $sql = "UPDATE {$this->table} 
                SET tokens_remaining = tokens_remaining - :tokens, updated_at = NOW() 
                WHERE user_id = :user_id AND status = 'active' AND tokens_remaining >= :tokens2";
        return $this->execute($sql, [
            'tokens' => $tokens,
            'user_id' => $userId,
            'tokens2' => $tokens
        ]);
    }
}
