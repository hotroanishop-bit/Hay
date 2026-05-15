<?php
/**
 * Campaign Registration Model
 * Tracks users who registered through campaigns
 */
class CampaignRegistration extends BaseModel
{
    protected string $table = 'campaign_registrations';
    protected array $fillable = [
        'campaign_id',
        'user_id',
        'bonus_received',
        'ip_address',
        'user_agent'
    ];

    /**
     * Check if user already registered through this campaign
     */
    public function hasUserRegistered(int $campaignId, int $userId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE campaign_id = :campaign_id AND user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'campaign_id' => $campaignId,
            'user_id' => $userId
        ]);
        $result = $stmt->fetch();
        return (int)$result['count'] > 0;
    }

    /**
     * Record a campaign registration
     */
    public function recordRegistration(int $campaignId, int $userId, float $bonus, ?string $ipAddress = null, ?string $userAgent = null): int
    {
        return $this->create([
            'campaign_id' => $campaignId,
            'user_id' => $userId,
            'bonus_received' => $bonus,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent
        ]);
    }

    /**
     * Get registrations for a campaign with user info
     */
    public function getRegistrationsForCampaign(int $campaignId, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT cr.*, u.name as user_name, u.email as user_email
                FROM {$this->table} cr
                JOIN users u ON cr.user_id = u.id
                WHERE cr.campaign_id = :campaign_id
                ORDER BY cr.registered_at DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':campaign_id', $campaignId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Count registrations for a campaign
     */
    public function countForCampaign(int $campaignId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE campaign_id = :campaign_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['campaign_id' => $campaignId]);
        $result = $stmt->fetch();
        return (int)$result['count'];
    }

    /**
     * Get user's campaign registrations
     */
    public function getUserRegistrations(int $userId): array
    {
        $sql = "SELECT cr.*, c.name as campaign_name, c.slug as campaign_slug
                FROM {$this->table} cr
                JOIN campaigns c ON cr.campaign_id = c.id
                WHERE cr.user_id = :user_id
                ORDER BY cr.registered_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }
}
