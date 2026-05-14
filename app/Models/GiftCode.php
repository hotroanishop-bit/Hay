<?php
/**
 * Gift Code Model
 * Handles gift code management and redemption
 */
class GiftCode extends BaseModel
{
    protected string $table = 'gift_codes';
    protected array $fillable = [
        'code',
        'type',
        'value',
        'max_uses',
        'used_count',
        'expires_at',
        'is_active',
        'created_by'
    ];

    /**
     * Find gift code by code string
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
     * Check if code is valid for redemption
     */
    public function isValidForRedemption(array $giftCode, int $userId): array
    {
        // Check if active
        if (!$giftCode['is_active']) {
            return ['valid' => false, 'error' => 'Gift code da bi vo hieu hoa'];
        }

        // Check expiration
        if ($giftCode['expires_at'] && strtotime($giftCode['expires_at']) < time()) {
            return ['valid' => false, 'error' => 'Gift code da het han'];
        }

        // Check max uses
        if ($giftCode['max_uses'] > 0 && $giftCode['used_count'] >= $giftCode['max_uses']) {
            return ['valid' => false, 'error' => 'Gift code da duoc su dung het'];
        }

        // Check if user already redeemed
        if ($this->hasUserRedeemed($giftCode['id'], $userId)) {
            return ['valid' => false, 'error' => 'Ban da su dung gift code nay roi'];
        }

        return ['valid' => true, 'error' => null];
    }

    /**
     * Check if user already redeemed this code
     */
    public function hasUserRedeemed(int $giftCodeId, int $userId): bool
    {
        $sql = "SELECT id FROM gift_code_redemptions 
                WHERE gift_code_id = :gift_code_id AND user_id = :user_id LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'gift_code_id' => $giftCodeId,
            'user_id' => $userId
        ]);
        return (bool) $stmt->fetch();
    }

    /**
     * Record redemption
     */
    public function recordRedemption(int $giftCodeId, int $userId, float $valueReceived): int
    {
        $sql = "INSERT INTO gift_code_redemptions (gift_code_id, user_id, value_received, redeemed_at)
                VALUES (:gift_code_id, :user_id, :value_received, NOW())";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'gift_code_id' => $giftCodeId,
            'user_id' => $userId,
            'value_received' => $valueReceived
        ]);
        return (int) $this->db()->lastInsertId();
    }

    /**
     * Increment used count
     */
    public function incrementUsedCount(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET used_count = used_count + 1 WHERE id = :id";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get user redemption history
     */
    public function getUserRedemptions(int $userId, int $limit = 20): array
    {
        $sql = "SELECT r.*, gc.code, gc.type, gc.value as original_value
                FROM gift_code_redemptions r
                JOIN gift_codes gc ON r.gift_code_id = gc.id
                WHERE r.user_id = :user_id
                ORDER BY r.redeemed_at DESC
                LIMIT :limit";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Get all gift codes with pagination (admin)
     */
    public function getAllWithPagination(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $offset = ($page - 1) * $perPage;
        $where = [];
        $params = [];

        if (!empty($filters['type'])) {
            $where[] = "type = :type";
            $params['type'] = $filters['type'];
        }

        if (!empty($filters['is_active'])) {
            $where[] = "is_active = :is_active";
            $params['is_active'] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $where[] = "code LIKE :search";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        // Get total count
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} {$whereClause}";
        $stmt = $this->db()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) $stmt->fetch()['total'];

        // Get data
        $sql = "SELECT gc.*, u.name as creator_name
                FROM {$this->table} gc
                LEFT JOIN users u ON gc.created_by = u.id
                {$whereClause}
                ORDER BY gc.created_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $data = $stmt->fetchAll();

        return [
            'data' => $data,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get redemptions for a gift code (admin)
     */
    public function getRedemptions(int $giftCodeId): array
    {
        $sql = "SELECT r.*, u.name as user_name, u.email as user_email
                FROM gift_code_redemptions r
                JOIN users u ON r.user_id = u.id
                WHERE r.gift_code_id = :gift_code_id
                ORDER BY r.redeemed_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['gift_code_id' => $giftCodeId]);
        return $stmt->fetchAll();
    }

    /**
     * Generate unique code
     */
    public function generateUniqueCode(string $prefix = 'GIFT', int $length = 8): string
    {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxAttempts = 10;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $code = $prefix;
            for ($i = 0; $i < $length; $i++) {
                $code .= $characters[random_int(0, strlen($characters) - 1)];
            }

            if (!$this->findByCode($code)) {
                return $code;
            }
        }

        // Fallback with timestamp
        return $prefix . strtoupper(substr(md5(uniqid()), 0, $length));
    }

    /**
     * Bulk create gift codes
     */
    public function bulkCreate(int $count, array $data, int $createdBy): array
    {
        $codes = [];
        $prefix = $data['prefix'] ?? 'GIFT';
        $length = $data['length'] ?? 8;

        $this->beginTransaction();

        try {
            for ($i = 0; $i < $count; $i++) {
                $code = $this->generateUniqueCode($prefix, $length);
                $id = $this->create([
                    'code' => $code,
                    'type' => $data['type'] ?? 'tokens',
                    'value' => $data['value'] ?? 0,
                    'max_uses' => $data['max_uses'] ?? 1,
                    'expires_at' => $data['expires_at'] ?? null,
                    'is_active' => 1,
                    'created_by' => $createdBy
                ]);
                $codes[] = ['id' => $id, 'code' => $code];
            }

            $this->commit();
            return ['success' => true, 'codes' => $codes];
        } catch (\Exception $e) {
            $this->rollback();
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active WHERE id = :id";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute(['id' => $id]);
    }

    /**
     * Get statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_codes,
                    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_codes,
                    SUM(used_count) as total_redemptions,
                    SUM(CASE WHEN type = 'tokens' THEN value * used_count ELSE 0 END) as tokens_given
                FROM {$this->table}";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch() ?: [];
    }
}
