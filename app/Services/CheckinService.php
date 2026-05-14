<?php
/**
 * Check-in Service
 * Business logic for daily check-in rewards
 */
class CheckinService
{
    private DailyCheckin $checkinModel;
    private User $userModel;

    // Reward configuration
    private const BASE_REWARD = 1.0;
    private const STREAK_BONUS_MULTIPLIER = 0.1; // 10% extra per day
    private const MAX_STREAK_BONUS = 2.0; // Max 2x reward
    private const WEEK_BONUS = 2.0; // 7-day streak bonus multiplier

    public function __construct()
    {
        $this->checkinModel = new DailyCheckin();
        $this->userModel = new User();
    }

    /**
     * Perform daily check-in for user
     */
    public function checkin(int $userId): array
    {
        // Check if already checked in today
        $todayCheckin = $this->checkinModel->getTodayCheckin($userId);
        if ($todayCheckin) {
            return [
                'success' => false,
                'message' => 'Ban da diem danh hom nay roi!',
                'already_checked_in' => true
            ];
        }

        // Calculate streak
        $yesterdayCheckin = $this->checkinModel->getYesterdayCheckin($userId);
        $streak = $yesterdayCheckin ? ((int) $yesterdayCheckin['streak_count'] + 1) : 1;

        // Calculate reward with streak bonus
        $reward = $this->calculateReward($streak);

        $this->checkinModel->beginTransaction();

        try {
            // Create check-in record
            $this->checkinModel->create([
                'user_id' => $userId,
                'checkin_date' => date('Y-m-d'),
                'streak_count' => $streak,
                'reward_amount' => $reward
            ]);

            // Add reward to user balance
            $sql = "UPDATE users SET balance = balance + :reward WHERE id = :user_id";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'reward' => $reward,
                'user_id' => $userId
            ]);

            // Record transaction
            $transactionSql = "INSERT INTO transactions (user_id, type, amount, description, status, created_at)
                               VALUES (:user_id, 'credit', :amount, :description, 'completed', NOW())";
            $stmt = $this->userModel->db()->prepare($transactionSql);
            $stmt->execute([
                'user_id' => $userId,
                'amount' => $reward,
                'description' => 'Daily check-in reward (Day ' . $streak . ')'
            ]);

            // Create notification
            $this->createCheckinNotification($userId, $streak, $reward);

            // Check for achievements
            $this->checkAchievements($userId, $streak);

            $this->checkinModel->commit();

            // Get updated user balance
            $user = $this->userModel->find($userId);

            return [
                'success' => true,
                'message' => 'Diem danh thanh cong!',
                'reward' => $reward,
                'streak' => $streak,
                'new_balance' => $user['balance'] ?? 0,
                'is_week_bonus' => ($streak % 7 === 0)
            ];
        } catch (\Exception $e) {
            $this->checkinModel->rollback();
            return [
                'success' => false,
                'message' => 'Loi khi diem danh: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Calculate reward based on streak
     */
    private function calculateReward(int $streak): float
    {
        $baseReward = self::BASE_REWARD;
        
        // Apply streak bonus
        $streakBonus = min(
            ($streak - 1) * self::STREAK_BONUS_MULTIPLIER,
            self::MAX_STREAK_BONUS - 1
        );
        
        $reward = $baseReward * (1 + $streakBonus);

        // Apply 7-day streak bonus
        if ($streak % 7 === 0) {
            $reward *= self::WEEK_BONUS;
        }

        return round($reward, 2);
    }

    /**
     * Create notification for check-in
     */
    private function createCheckinNotification(int $userId, int $streak, float $reward): void
    {
        try {
            $title = 'Diem danh thanh cong!';
            $message = 'Ban nhan duoc ' . number_format($reward, 2) . ' credits (Streak: ' . $streak . ' ngay)';

            if ($streak % 7 === 0) {
                $message .= ' - Bonus x2 tuan!';
            }

            $sql = "INSERT INTO user_notifications (user_id, type, title, message, created_at)
                    VALUES (:user_id, 'success', :title, :message, NOW())";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute([
                'user_id' => $userId,
                'title' => $title,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Check and award achievements for streaks
     */
    private function checkAchievements(int $userId, int $streak): void
    {
        try {
            // Find achievements related to check-in streaks
            $sql = "SELECT * FROM achievements 
                    WHERE condition_type = 'checkin_streak' 
                    AND condition_value <= :streak 
                    AND is_active = 1";
            $stmt = $this->userModel->db()->prepare($sql);
            $stmt->execute(['streak' => $streak]);
            $achievements = $stmt->fetchAll();

            foreach ($achievements as $achievement) {
                // Check if user already has this achievement
                $checkSql = "SELECT id FROM user_achievements 
                            WHERE user_id = :user_id AND achievement_id = :achievement_id";
                $checkStmt = $this->userModel->db()->prepare($checkSql);
                $checkStmt->execute([
                    'user_id' => $userId,
                    'achievement_id' => $achievement['id']
                ]);

                if (!$checkStmt->fetch()) {
                    // Award achievement
                    $awardSql = "INSERT INTO user_achievements (user_id, achievement_id, unlocked_at)
                                VALUES (:user_id, :achievement_id, NOW())";
                    $awardStmt = $this->userModel->db()->prepare($awardSql);
                    $awardStmt->execute([
                        'user_id' => $userId,
                        'achievement_id' => $achievement['id']
                    ]);

                    // Add reward tokens if any
                    if ($achievement['reward_tokens'] > 0) {
                        $rewardSql = "UPDATE users SET balance = balance + :reward WHERE id = :user_id";
                        $rewardStmt = $this->userModel->db()->prepare($rewardSql);
                        $rewardStmt->execute([
                            'reward' => $achievement['reward_tokens'],
                            'user_id' => $userId
                        ]);
                    }
                }
            }
        } catch (\Exception $e) {
            // Silent fail
        }
    }

    /**
     * Get check-in data for user
     */
    public function getCheckinData(int $userId): array
    {
        return [
            'today_checked' => $this->checkinModel->getTodayCheckin($userId) !== null,
            'current_streak' => $this->checkinModel->getCurrentStreak($userId),
            'max_streak' => $this->checkinModel->getMaxStreak($userId),
            'total_checkins' => $this->checkinModel->getTotalCheckins($userId),
            'total_rewards' => $this->checkinModel->getTotalRewards($userId),
            'monthly_checkins' => $this->checkinModel->getMonthlyCheckins($userId),
            'recent_checkins' => $this->checkinModel->getRecentCheckins($userId, 7),
            'next_reward' => $this->calculateNextReward($userId)
        ];
    }

    /**
     * Calculate next check-in reward
     */
    private function calculateNextReward(int $userId): float
    {
        $todayCheckin = $this->checkinModel->getTodayCheckin($userId);
        if ($todayCheckin) {
            // Calculate tomorrow's reward
            $streak = (int) $todayCheckin['streak_count'] + 1;
        } else {
            // Calculate today's reward
            $yesterdayCheckin = $this->checkinModel->getYesterdayCheckin($userId);
            $streak = $yesterdayCheckin ? ((int) $yesterdayCheckin['streak_count'] + 1) : 1;
        }

        return $this->calculateReward($streak);
    }

    /**
     * Get calendar data for display
     */
    public function getCalendarData(int $userId, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) date('Y');
        $month = $month ?? (int) date('m');
        
        $checkins = $this->checkinModel->getMonthlyCheckins($userId, $year, $month);
        
        // Convert to date => data format
        $checkinDates = [];
        foreach ($checkins as $checkin) {
            $checkinDates[$checkin['checkin_date']] = [
                'streak' => $checkin['streak_count'],
                'reward' => $checkin['reward_amount']
            ];
        }

        return [
            'year' => $year,
            'month' => $month,
            'checkins' => $checkinDates,
            'first_day' => date('w', strtotime("$year-$month-01")),
            'days_in_month' => date('t', strtotime("$year-$month-01"))
        ];
    }
}
