<?php
/**
 * Referral Model
 * Handles referral tracking and commission management
 */

class Referral extends BaseModel
{
    protected string $table = 'referrals';
    
    protected array $fillable = [
        'referrer_id',
        'referred_id',
        'commission_earned',
        'status',
        'created_at',
        'updated_at'
    ];

    // Referral statuses
    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_PAID = 'paid';

    /**
     * Create a new referral
     */
    public function createReferral(int $referrerId, int $referredId): int
    {
        return $this->create([
            'referrer_id' => $referrerId,
            'referred_id' => $referredId,
            'commission_earned' => 0.00,
            'status' => self::STATUS_PENDING,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all referrals by referrer user ID
     */
    public function getReferralsByUser(int $userId, int $limit = 50, int $offset = 0): array
    {
        $sql = "SELECT r.*, u.name as referred_name, u.email as referred_email, u.created_at as referred_created_at
                FROM {$this->table} r
                JOIN users u ON r.referred_id = u.id
                WHERE r.referrer_id = :user_id
                ORDER BY r.created_at DESC
                LIMIT {$limit} OFFSET {$offset}";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Count referrals by user
     */
    public function countReferralsByUser(int $userId): int
    {
        return $this->count(['referrer_id' => $userId]);
    }

    /**
     * Get total earnings for a user
     */
    public function getTotalEarnings(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(commission_earned), 0) as total 
                FROM {$this->table} 
                WHERE referrer_id = :user_id";
        
        $result = $this->query($sql, ['user_id' => $userId]);
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Get pending earnings for a user (approved but not paid)
     */
    public function getPendingEarnings(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(commission_earned), 0) as total 
                FROM {$this->table} 
                WHERE referrer_id = :user_id AND status = :status";
        
        $result = $this->query($sql, [
            'user_id' => $userId,
            'status' => self::STATUS_APPROVED
        ]);
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Get approved earnings for a user
     */
    public function getApprovedEarnings(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(commission_earned), 0) as total 
                FROM {$this->table} 
                WHERE referrer_id = :user_id AND status IN (:status1, :status2)";
        
        $result = $this->query($sql, [
            'user_id' => $userId,
            'status1' => self::STATUS_APPROVED,
            'status2' => self::STATUS_PAID
        ]);
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Update commission for a referral
     */
    public function updateCommission(int $referralId, float $amount): bool
    {
        $sql = "UPDATE {$this->table} 
                SET commission_earned = commission_earned + :amount, 
                    updated_at = NOW() 
                WHERE id = :id";
        
        return $this->execute($sql, [
            'amount' => $amount,
            'id' => $referralId
        ]);
    }

    /**
     * Approve a referral
     */
    public function approve(int $referralId): bool
    {
        return $this->update($referralId, [
            'status' => self::STATUS_APPROVED,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Mark referral as paid
     */
    public function markPaid(int $referralId): bool
    {
        return $this->update($referralId, [
            'status' => self::STATUS_PAID,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Find referral by referred user ID
     */
    public function findByReferredUser(int $referredUserId): ?array
    {
        return $this->findBy(['referred_id' => $referredUserId]);
    }

    /**
     * Get referral stats for a user
     */
    public function getStats(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_referrals,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
                    SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_count,
                    COALESCE(SUM(commission_earned), 0) as total_earned,
                    COALESCE(SUM(CASE WHEN status = 'approved' THEN commission_earned ELSE 0 END), 0) as available_to_withdraw
                FROM {$this->table}
                WHERE referrer_id = :user_id";
        
        $result = $this->query($sql, ['user_id' => $userId]);
        
        return $result[0] ?? [
            'total_referrals' => 0,
            'pending_count' => 0,
            'approved_count' => 0,
            'paid_count' => 0,
            'total_earned' => 0,
            'available_to_withdraw' => 0
        ];
    }

    /**
     * Mark all approved referrals as paid for a user
     */
    public function markAllApprovedAsPaid(int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = :paid_status, updated_at = NOW() 
                WHERE referrer_id = :user_id AND status = :approved_status";
        
        return $this->execute($sql, [
            'paid_status' => self::STATUS_PAID,
            'user_id' => $userId,
            'approved_status' => self::STATUS_APPROVED
        ]);
    }

    /**
     * Get paginated referrals with referred user details
     */
    public function getPaginatedReferrals(int $userId, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $referrals = $this->getReferralsByUser($userId, $perPage, $offset);
        $total = $this->countReferralsByUser($userId);
        
        return [
            'data' => $referrals,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage)
        ];
    }
}
