<?php
/**
 * Plan Model
 * Handles subscription plans for API access
 */

class Plan extends BaseModel
{
    protected string $table = 'plans';
    
    protected array $fillable = [
        'name',
        'price_monthly',
        'rate_limit_per_minute',
        'daily_token_limit',
        'price_multiplier',
        'token_quota',
        'duration_days',
        'is_free',
        'description',
        'is_active',
        'created_at',
        'updated_at'
    ];

    /**
     * Find all active plans
     */
    public function findActive(): array
    {
        return $this->findAll(['is_active' => 1], 'price_monthly ASC');
    }

    /**
     * Find a plan by name
     */
    public function findByName(string $name): ?array
    {
        return $this->findBy(['name' => $name]);
    }

    /**
     * Get the default plan (Basic)
     */
    public function getDefaultPlan(): ?array
    {
        return $this->findByName('Basic');
    }

    /**
     * Get a user's plan or return default if none assigned
     */
    public function getUserPlan(int $userId): ?array
    {
        $sql = "SELECT p.* FROM {$this->table} p 
                INNER JOIN users u ON u.plan_id = p.id 
                WHERE u.id = :user_id";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        
        if ($result) {
            return $result;
        }
        
        // Return default plan if user has no plan assigned
        return $this->getDefaultPlan();
    }

    /**
     * Assign a plan to a user
     */
    public function assignToUser(int $userId, int $planId): bool
    {
        $sql = "UPDATE users SET plan_id = :plan_id, updated_at = NOW() WHERE id = :user_id";
        return $this->execute($sql, ['plan_id' => $planId, 'user_id' => $userId]);
    }

    /**
     * Get users count for a specific plan
     */
    public function getUsersCount(int $planId): int
    {
        $sql = "SELECT COUNT(*) as count FROM users WHERE plan_id = :plan_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['plan_id' => $planId]);
        $result = $stmt->fetch();
        
        return (int) $result['count'];
    }

    /**
     * Get token quota for a plan
     */
    public function getTokenQuota(int $planId): int
    {
        $plan = $this->find($planId);
        
        if (!$plan) {
            return 0;
        }
        
        return (int) ($plan['token_quota'] ?? 0);
    }

    /**
     * Parse and set token quota using token notation (e.g., 10k, 1M)
     * Requires TokenHelper to be included
     */
    public function parseAndSetTokenQuota(int $planId, string $notation): bool
    {
        // Include TokenHelper if not already loaded
        if (!function_exists('parseTokenNotation')) {
            $helperPath = dirname(__DIR__) . '/Helpers/TokenHelper.php';
            if (file_exists($helperPath)) {
                require_once $helperPath;
            }
        }
        
        $tokens = function_exists('parseTokenNotation') 
            ? parseTokenNotation($notation) 
            : (int) $notation;
        
        $sql = "UPDATE {$this->table} SET token_quota = :tokens, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['tokens' => $tokens, 'id' => $planId]);
    }

    /**
     * Find all free plans
     */
    public function findFreePlans(): array
    {
        return $this->findAll(['is_free' => 1, 'is_active' => 1], 'name ASC');
    }

    /**
     * Find all paid plans
     */
    public function findPaidPlans(): array
    {
        return $this->findAll(['is_free' => 0, 'is_active' => 1], 'price_monthly ASC');
    }
}
