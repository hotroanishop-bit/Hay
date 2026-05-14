<?php
/**
 * Achievement Model
 * Handles achievements and user badges
 */
class Achievement extends BaseModel
{
    protected string $table = 'achievements';
    protected array $fillable = [
        'name',
        'description',
        'icon',
        'condition_type',
        'condition_value',
        'reward_tokens',
        'is_active',
        'sort_order'
    ];

    /**
     * Get all active achievements
     */
    public function getAllActive(): array
    {
        return $this->findAll(['is_active' => 1], 'sort_order ASC');
    }

    /**
     * Get user's unlocked achievements
     */
    public function getUserAchievements(int $userId): array
    {
        $sql = "SELECT a.*, ua.unlocked_at
                FROM {$this->table} a
                INNER JOIN user_achievements ua ON a.id = ua.achievement_id
                WHERE ua.user_id = :user_id AND a.is_active = 1
                ORDER BY ua.unlocked_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get all achievements with user progress
     */
    public function getAllWithUserProgress(int $userId): array
    {
        $sql = "SELECT a.*, 
                    ua.unlocked_at,
                    CASE WHEN ua.id IS NOT NULL THEN 1 ELSE 0 END as is_unlocked
                FROM {$this->table} a
                LEFT JOIN user_achievements ua ON a.id = ua.achievement_id AND ua.user_id = :user_id
                WHERE a.is_active = 1
                ORDER BY a.sort_order ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    /**
     * Check if user has achievement
     */
    public function userHasAchievement(int $userId, int $achievementId): bool
    {
        $sql = "SELECT id FROM user_achievements 
                WHERE user_id = :user_id AND achievement_id = :achievement_id LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'achievement_id' => $achievementId
        ]);
        return (bool) $stmt->fetch();
    }

    /**
     * Award achievement to user
     */
    public function awardToUser(int $userId, int $achievementId): bool
    {
        if ($this->userHasAchievement($userId, $achievementId)) {
            return false;
        }

        $sql = "INSERT INTO user_achievements (user_id, achievement_id, unlocked_at)
                VALUES (:user_id, :achievement_id, NOW())";
        $stmt = $this->db()->prepare($sql);
        return $stmt->execute([
            'user_id' => $userId,
            'achievement_id' => $achievementId
        ]);
    }

    /**
     * Count user achievements
     */
    public function countUserAchievements(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM user_achievements WHERE user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetch()['count'];
    }

    /**
     * Get achievements by condition type
     */
    public function getByConditionType(string $conditionType): array
    {
        return $this->findAll(['condition_type' => $conditionType, 'is_active' => 1], 'condition_value ASC');
    }

    /**
     * Get recent unlocks for user
     */
    public function getRecentUnlocks(int $userId, int $limit = 5): array
    {
        $sql = "SELECT a.*, ua.unlocked_at
                FROM {$this->table} a
                INNER JOIN user_achievements ua ON a.id = ua.achievement_id
                WHERE ua.user_id = :user_id AND a.is_active = 1
                ORDER BY ua.unlocked_at DESC
                LIMIT :limit";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
