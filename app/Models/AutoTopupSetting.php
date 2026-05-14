<?php
/**
 * Auto Top-Up Setting Model
 * Handles auto top-up configuration for users
 */

class AutoTopupSetting extends BaseModel
{
    protected string $table = 'auto_topup_settings';
    protected array $fillable = [
        'user_id',
        'threshold',
        'amount',
        'is_active',
        'cooldown_hours',
        'last_triggered_at'
    ];

    /**
     * Get auto top-up settings for a user
     */
    public function getByUser(int $userId): ?array
    {
        return $this->findBy(['user_id' => $userId]);
    }

    /**
     * Create or update settings for a user
     */
    public function upsert(int $userId, array $data): int
    {
        $existing = $this->getByUser($userId);
        
        if ($existing) {
            $this->update($existing['id'], $data);
            return $existing['id'];
        }
        
        $data['user_id'] = $userId;
        return $this->create($data);
    }

    /**
     * Check if auto top-up should trigger based on settings and current balance
     */
    public function shouldTrigger(array $setting, float $currentBalance): bool
    {
        // Must be active
        if (empty($setting['is_active'])) {
            return false;
        }

        // Check if balance is below threshold
        if ($currentBalance >= (float) $setting['threshold']) {
            return false;
        }

        // Check cooldown period
        if (!empty($setting['last_triggered_at'])) {
            $lastTriggered = strtotime($setting['last_triggered_at']);
            $cooldownSeconds = ((int) ($setting['cooldown_hours'] ?? 24)) * 3600;
            
            if (time() < ($lastTriggered + $cooldownSeconds)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Record that auto top-up was triggered
     */
    public function recordTrigger(int $settingId): bool
    {
        $sql = "UPDATE {$this->table} SET last_triggered_at = NOW(), updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['id' => $settingId]);
    }

    /**
     * Toggle the active status
     */
    public function toggleActive(int $userId): bool
    {
        $setting = $this->getByUser($userId);
        
        if (!$setting) {
            return false;
        }

        $newStatus = $setting['is_active'] ? 0 : 1;
        return $this->update($setting['id'], ['is_active' => $newStatus]);
    }

    /**
     * Get all active settings that need to be checked
     * This can be used by a cron job to proactively check balances
     */
    public function getActiveSettings(): array
    {
        $sql = "SELECT ats.*, u.balance, u.email, u.name 
                FROM {$this->table} ats
                JOIN users u ON ats.user_id = u.id
                WHERE ats.is_active = 1";
        
        return $this->query($sql, []);
    }

    /**
     * Delete settings for a user
     */
    public function deleteByUser(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        return $this->execute($sql, ['user_id' => $userId]);
    }
}
