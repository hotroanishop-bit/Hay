<?php
/**
 * Achievement Service
 * Business logic for achievements and badges
 */
class AchievementService
{
    private Achievement $achievementModel;
    private User $userModel;

    public function __construct()
    {
        $this->achievementModel = new Achievement();
        $this->userModel = new User();
    }

    /**
     * Get all achievements with user progress
     */
    public function getAchievementsWithProgress(int $userId): array
    {
        $achievements = $this->achievementModel->getAllWithUserProgress($userId);
        
        // Calculate user progress for each achievement
        foreach ($achievements as &$achievement) {
            $achievement['progress'] = $this->calculateProgress($userId, $achievement);
        }

        return $achievements;
    }

    /**
     * Calculate user progress for an achievement
     */
    private function calculateProgress(int $userId, array $achievement): array
    {
        $current = 0;
        $target = (int) $achievement['condition_value'];

        switch ($achievement['condition_type']) {
            case 'first_deposit':
                $sql = "SELECT COUNT(*) as count FROM transactions 
                        WHERE user_id = :user_id AND type = 'credit' AND description LIKE '%deposit%'";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $current = min((int) $stmt->fetch()['count'], 1);
                break;

            case 'api_calls':
                $sql = "SELECT COUNT(*) as count FROM usage_logs WHERE user_id = :user_id";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $current = (int) $stmt->fetch()['count'];
                break;

            case 'referrals':
                $sql = "SELECT COUNT(*) as count FROM referrals WHERE referrer_id = :user_id AND status = 'completed'";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $current = (int) $stmt->fetch()['count'];
                break;

            case 'checkin_streak':
                $checkinModel = new DailyCheckin();
                $current = $checkinModel->getMaxStreak($userId);
                break;

            case 'total_spent':
                $sql = "SELECT SUM(amount) as total FROM transactions 
                        WHERE user_id = :user_id AND type = 'credit' AND description LIKE '%deposit%'";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $current = (int) ($stmt->fetch()['total'] ?? 0);
                break;

            case 'first_key':
                $sql = "SELECT COUNT(*) as count FROM api_keys WHERE user_id = :user_id";
                $stmt = $this->userModel->db()->prepare($sql);
                $stmt->execute(['user_id' => $userId]);
                $current = min((int) $stmt->fetch()['count'], 1);
                break;

            default:
                $current = 0;
        }

        return [
            'current' => $current,
            'target' => $target,
            'percentage' => min(100, round(($current / max($target, 1)) * 100))
        ];
    }

    /**
     * Check and award achievements for user based on action
     */
    public function checkAndAward(int $userId, string $actionType, $actionValue = null): array
    {
        $awardedAchievements = [];

        // Map action types to condition types
        $conditionTypeMap = [
            'deposit' => 'first_deposit',
            'api_call' => 'api_calls',
            'referral' => 'referrals',
            'checkin' => 'checkin_streak',
            'spend' => 'total_spent',
            'create_key' => 'first_key'
        ];

        $conditionType = $conditionTypeMap[$actionType] ?? null;
        if (!$conditionType) {
            return $awardedAchievements;
        }

        // Get achievements for this condition type
        $achievements = $this->achievementModel->getByConditionType($conditionType);

        foreach ($achievements as $achievement) {
            if ($this->achievementModel->userHasAchievement($userId, $achievement['id'])) {
                continue;
            }

            $progress = $this->calculateProgress($userId, $achievement);
            
            if ($progress['current'] >= $progress['target']) {
                // Award achievement
                $this->achievementModel->awardToUser($userId, $achievement['id']);

                // Award reward tokens
                if ($achievement['reward_tokens'] > 0) {
                    $sql = "UPDATE users SET balance = balance + :reward WHERE id = :user_id";
                    $stmt = $this->userModel->db()->prepare($sql);
                    $stmt->execute([
                        'reward' => $achievement['reward_tokens'],
                        'user_id' => $userId
                    ]);

                    // Record transaction
                    $transactionSql = "INSERT INTO transactions (user_id, type, amount, description, status, created_at)
                                       VALUES (:user_id, 'credit', :amount, :description, 'completed', NOW())";
                    $stmt = $this->userModel->db()->prepare($transactionSql);
                    $stmt->execute([
                        'user_id' => $userId,
                        'amount' => $achievement['reward_tokens'],
                        'description' => 'Achievement reward: ' . $achievement['name']
                    ]);
                }

                // Create notification
                $this->createAchievementNotification($userId, $achievement);

                $awardedAchievements[] = $achievement;
            }
        }

        return $awardedAchievements;
    }

    /**
     * Create notification for achievement unlock
     */
    private function createAchievementNotification(int $userId, array $achievement): void
    {
        try {
            $sql = "INSERT INTO user_notifications (user_id, type, title, message, data, created_at)
                    VALUES (:user_id, 'success', :title, :message, :data, NOW())";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'title' => 'Mo khoa thanh tuu moi!',
                'message' => 'Ban da dat duoc: ' . $achievement['name'] . ($achievement['reward_tokens'] > 0 ? ' (+' . $achievement['reward_tokens'] . ' tokens)' : ''),
                'data' => json_encode(['achievement_id' => $achievement['id']])
            ]);
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Get user achievement stats
     */
    public function getUserStats(int $userId): array
    {
        $totalAchievements = $this->achievementModel->count(['is_active' => 1]);
        $unlockedCount = $this->achievementModel->countUserAchievements($userId);

        // Calculate total rewards earned
        $sql = "SELECT SUM(a.reward_tokens) as total
                FROM achievements a
                INNER JOIN user_achievements ua ON a.id = ua.achievement_id
                WHERE ua.user_id = :user_id";
        $stmt = $this->userModel->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $totalRewards = (float) ($stmt->fetch()['total'] ?? 0);

        return [
            'total' => $totalAchievements,
            'unlocked' => $unlockedCount,
            'percentage' => round(($unlockedCount / max($totalAchievements, 1)) * 100),
            'total_rewards' => $totalRewards,
            'recent' => $this->achievementModel->getRecentUnlocks($userId, 3)
        ];
    }

    /**
     * Get categorized achievements
     */
    public function getCategorizedAchievements(int $userId): array
    {
        $achievements = $this->getAchievementsWithProgress($userId);
        
        $categories = [
            'beginner' => ['label' => 'Nguoi moi', 'items' => []],
            'activity' => ['label' => 'Hoat dong', 'items' => []],
            'loyalty' => ['label' => 'Trung thanh', 'items' => []],
            'spending' => ['label' => 'Chi tieu', 'items' => []]
        ];

        foreach ($achievements as $achievement) {
            switch ($achievement['condition_type']) {
                case 'first_deposit':
                case 'first_key':
                    $categories['beginner']['items'][] = $achievement;
                    break;
                case 'api_calls':
                case 'referrals':
                    $categories['activity']['items'][] = $achievement;
                    break;
                case 'checkin_streak':
                    $categories['loyalty']['items'][] = $achievement;
                    break;
                case 'total_spent':
                    $categories['spending']['items'][] = $achievement;
                    break;
            }
        }

        return $categories;
    }
}
