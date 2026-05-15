<?php
/**
 * Campaign Model
 * Handles registration campaigns data
 */
class Campaign extends BaseModel
{
    protected string $table = 'campaigns';
    protected array $fillable = [
        'name',
        'slug',
        'description',
        'bonus_tokens',
        'bonus_credits',
        'max_registrations',
        'current_registrations',
        'starts_at',
        'expires_at',
        'is_active',
        'created_by'
    ];

    /**
     * Find campaign by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get all campaigns with creator info
     */
    public function getAllWithCreator(string $orderBy = 'created_at DESC'): array
    {
        $sql = "SELECT c.*, u.name as creator_name, u.email as creator_email
                FROM {$this->table} c
                LEFT JOIN users u ON c.created_by = u.id
                ORDER BY {$orderBy}";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get active campaigns
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND (starts_at IS NULL OR starts_at <= NOW())
                AND (expires_at IS NULL OR expires_at >= NOW())
                ORDER BY created_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Toggle campaign active status
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Increment registration count
     */
    public function incrementRegistrations(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET current_registrations = current_registrations + 1 WHERE id = :id";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Check if slug exists (for uniqueness validation)
     */
    public function slugExists(string $slug, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE slug = :slug";
        $params = ['slug' => $slug];
        
        if ($excludeId) {
            $sql .= " AND id != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return (int)$result['count'] > 0;
    }

    /**
     * Get campaign statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_campaigns,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_campaigns,
                    SUM(current_registrations) as total_registrations,
                    SUM(bonus_tokens * current_registrations) as total_tokens_given,
                    SUM(bonus_credits * current_registrations) as total_credits_given
                FROM {$this->table}";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [
            'total_campaigns' => 0,
            'active_campaigns' => 0,
            'total_registrations' => 0,
            'total_tokens_given' => 0,
            'total_credits_given' => 0
        ];
    }
}
