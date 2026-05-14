<?php
/**
 * Daily Check-in Model
 * Handles daily check-in records and streak tracking
 */
class DailyCheckin extends BaseModel
{
    protected string $table = 'daily_checkins';
    protected array $fillable = [
        'user_id',
        'checkin_date',
        'streak_count',
        'reward_amount'
    ];

    /**
     * Get today's check-in for user
     */
    public function getTodayCheckin(int $userId): ?array
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND checkin_date = :today LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'today' => $today]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get yesterday's check-in for user
     */
    public function getYesterdayCheckin(int $userId): ?array
    {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND checkin_date = :yesterday LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'yesterday' => $yesterday]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Get current streak for user
     */
    public function getCurrentStreak(int $userId): int
    {
        // Get the latest check-in
        $sql = "SELECT streak_count FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY checkin_date DESC 
                LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();

        if (!$result) {
            return 0;
        }

        // Check if streak is still valid (checked in today or yesterday)
        $todayCheckin = $this->getTodayCheckin($userId);
        $yesterdayCheckin = $this->getYesterdayCheckin($userId);

        if ($todayCheckin || $yesterdayCheckin) {
            return (int) $result['streak_count'];
        }

        return 0;
    }

    /**
     * Get check-ins for current month
     */
    public function getMonthlyCheckins(int $userId, ?int $year = null, ?int $month = null): array
    {
        $year = $year ?? (int) date('Y');
        $month = $month ?? (int) date('m');
        
        $startDate = sprintf('%04d-%02d-01', $year, $month);
        $endDate = date('Y-m-t', strtotime($startDate));

        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND checkin_date BETWEEN :start_date AND :end_date
                ORDER BY checkin_date ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute([
            'user_id' => $userId,
            'start_date' => $startDate,
            'end_date' => $endDate
        ]);
        
        return $stmt->fetchAll();
    }

    /**
     * Get total check-ins count for user
     */
    public function getTotalCheckins(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return (int) $stmt->fetch()['count'];
    }

    /**
     * Get max streak for user
     */
    public function getMaxStreak(int $userId): int
    {
        $sql = "SELECT MAX(streak_count) as max_streak FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return (int) ($result['max_streak'] ?? 0);
    }

    /**
     * Get total rewards earned from check-ins
     */
    public function getTotalRewards(int $userId): float
    {
        $sql = "SELECT SUM(reward_amount) as total FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        return (float) ($result['total'] ?? 0);
    }

    /**
     * Get recent check-ins
     */
    public function getRecentCheckins(int $userId, int $limit = 30): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY checkin_date DESC 
                LIMIT :limit";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
