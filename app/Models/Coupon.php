<?php
/**
 * Coupon Model
 * Handles coupon/promo code data operations
 */

class Coupon extends BaseModel
{
    protected string $table = 'coupons';
    
    protected array $fillable = [
        'code',
        'type',
        'value',
        'min_amount',
        'max_uses',
        'used_count',
        'max_uses_per_user',
        'expires_at',
        'is_active',
        'description',
        'created_at',
        'updated_at'
    ];

    /**
     * Find a coupon by code
     */
    public function findByCode(string $code): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE code = :code LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['code' => strtoupper(trim($code))]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Check if a coupon is valid for a user and amount
     * Returns array with 'valid' boolean and 'message' string
     */
    public function isValid(?array $coupon, int $userId, float $depositAmount): array
    {
        if (!$coupon) {
            return ['valid' => false, 'message' => 'Coupon not found'];
        }

        // Check if active
        if (empty($coupon['is_active'])) {
            return ['valid' => false, 'message' => 'This coupon is no longer active'];
        }

        // Check expiration
        if (!empty($coupon['expires_at']) && strtotime($coupon['expires_at']) < time()) {
            return ['valid' => false, 'message' => 'This coupon has expired'];
        }

        // Check minimum amount
        $minAmount = (float)($coupon['min_amount'] ?? 0);
        if ($minAmount > 0 && $depositAmount < $minAmount) {
            return [
                'valid' => false, 
                'message' => 'Minimum deposit amount is ' . number_format($minAmount) . ' VND'
            ];
        }

        // Check max total uses
        $maxUses = (int)($coupon['max_uses'] ?? 0);
        $usedCount = (int)($coupon['used_count'] ?? 0);
        if ($maxUses > 0 && $usedCount >= $maxUses) {
            return ['valid' => false, 'message' => 'This coupon has reached its usage limit'];
        }

        // Check user usage limit
        $maxUsesPerUser = (int)($coupon['max_uses_per_user'] ?? 1);
        $userUsageCount = $this->getUserUsageCount($coupon['id'], $userId);
        if ($maxUsesPerUser > 0 && $userUsageCount >= $maxUsesPerUser) {
            return ['valid' => false, 'message' => 'You have already used this coupon'];
        }

        return ['valid' => true, 'message' => 'Coupon is valid'];
    }

    /**
     * Get user's usage count for a coupon
     */
    public function getUserUsageCount(int $couponId, int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM coupon_usages 
                WHERE coupon_id = :coupon_id AND user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['coupon_id' => $couponId, 'user_id' => $userId]);
        $result = $stmt->fetch();
        
        return (int)($result['count'] ?? 0);
    }

    /**
     * Increment coupon usage count
     */
    public function incrementUsage(int $couponId): bool
    {
        $sql = "UPDATE {$this->table} SET used_count = used_count + 1, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $couponId]);
    }

    /**
     * Get all coupons with usage stats
     */
    public function getAllWithStats(): array
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM coupon_usages cu WHERE cu.coupon_id = c.id) as total_usages,
                       (SELECT COALESCE(SUM(cu.amount_saved), 0) FROM coupon_usages cu WHERE cu.coupon_id = c.id) as total_savings
                FROM {$this->table} c 
                ORDER BY c.created_at DESC";
        return $this->query($sql, []);
    }

    /**
     * Get active coupons
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND (expires_at IS NULL OR expires_at > NOW())
                AND (max_uses IS NULL OR max_uses = 0 OR used_count < max_uses)
                ORDER BY created_at DESC";
        return $this->query($sql, []);
    }

    /**
     * Toggle coupon active status
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    /**
     * Get coupon statistics
     */
    public function getStats(int $couponId): array
    {
        $coupon = $this->find($couponId);
        if (!$coupon) {
            return [];
        }

        // Get usage statistics
        $sql = "SELECT 
                    COUNT(*) as total_uses,
                    COUNT(DISTINCT user_id) as unique_users,
                    COALESCE(SUM(amount_saved), 0) as total_savings,
                    MIN(used_at) as first_used,
                    MAX(used_at) as last_used
                FROM coupon_usages 
                WHERE coupon_id = :coupon_id";
        $statsResult = $this->query($sql, ['coupon_id' => $couponId]);
        $stats = $statsResult[0] ?? [];

        // Get recent usages
        $sql = "SELECT cu.*, u.name as user_name, u.email as user_email
                FROM coupon_usages cu
                LEFT JOIN users u ON cu.user_id = u.id
                WHERE cu.coupon_id = :coupon_id
                ORDER BY cu.used_at DESC
                LIMIT 10";
        $recentUsages = $this->query($sql, ['coupon_id' => $couponId]);

        return [
            'coupon' => $coupon,
            'stats' => $stats,
            'recent_usages' => $recentUsages
        ];
    }

    /**
     * Create a new coupon
     */
    public function createCoupon(array $data): int
    {
        // Ensure code is uppercase
        $data['code'] = strtoupper(trim($data['code']));
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Update a coupon
     */
    public function updateCoupon(int $id, array $data): bool
    {
        // Ensure code is uppercase if provided
        if (isset($data['code'])) {
            $data['code'] = strtoupper(trim($data['code']));
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Check if code exists (excluding a specific coupon ID)
     */
    public function codeExists(string $code, ?int $excludeId = null): bool
    {
        $code = strtoupper(trim($code));
        $sql = "SELECT id FROM {$this->table} WHERE code = :code";
        $params = ['code' => $code];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->query($sql, $params);
        return !empty($result);
    }
}
