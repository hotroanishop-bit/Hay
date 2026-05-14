<?php
/**
 * CouponUsage Model
 * Tracks coupon usage by users
 */

class CouponUsage extends BaseModel
{
    protected string $table = 'coupon_usages';
    
    protected array $fillable = [
        'coupon_id',
        'user_id',
        'deposit_id',
        'amount_saved',
        'used_at'
    ];

    /**
     * Record a coupon usage
     */
    public function recordUsage(int $couponId, int $userId, ?int $depositId, float $amountSaved): int
    {
        return $this->create([
            'coupon_id' => $couponId,
            'user_id' => $userId,
            'deposit_id' => $depositId,
            'amount_saved' => $amountSaved,
            'used_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get usages by user
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT cu.*, c.code, c.type, c.value, c.description
                FROM {$this->table} cu
                LEFT JOIN coupons c ON cu.coupon_id = c.id
                WHERE cu.user_id = :user_id
                ORDER BY cu.used_at DESC";
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Get usages by coupon
     */
    public function getByCoupon(int $couponId): array
    {
        $sql = "SELECT cu.*, u.name as user_name, u.email as user_email, d.reference_code as deposit_ref
                FROM {$this->table} cu
                LEFT JOIN users u ON cu.user_id = u.id
                LEFT JOIN deposits d ON cu.deposit_id = d.id
                WHERE cu.coupon_id = :coupon_id
                ORDER BY cu.used_at DESC";
        return $this->query($sql, ['coupon_id' => $couponId]);
    }

    /**
     * Get total savings by user
     */
    public function getTotalSavingsByUser(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(amount_saved), 0) as total FROM {$this->table} WHERE user_id = :user_id";
        $result = $this->query($sql, ['user_id' => $userId]);
        return (float)($result[0]['total'] ?? 0);
    }

    /**
     * Get total savings by coupon
     */
    public function getTotalSavingsByCoupon(int $couponId): float
    {
        $sql = "SELECT COALESCE(SUM(amount_saved), 0) as total FROM {$this->table} WHERE coupon_id = :coupon_id";
        $result = $this->query($sql, ['coupon_id' => $couponId]);
        return (float)($result[0]['total'] ?? 0);
    }

    /**
     * Check if user has used a coupon
     */
    public function hasUserUsedCoupon(int $userId, int $couponId): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE user_id = :user_id AND coupon_id = :coupon_id LIMIT 1";
        $result = $this->query($sql, ['user_id' => $userId, 'coupon_id' => $couponId]);
        return !empty($result);
    }

    /**
     * Get recent usages across all coupons
     */
    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT cu.*, c.code, c.type, u.name as user_name, u.email as user_email
                FROM {$this->table} cu
                LEFT JOIN coupons c ON cu.coupon_id = c.id
                LEFT JOIN users u ON cu.user_id = u.id
                ORDER BY cu.used_at DESC
                LIMIT :limit";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
