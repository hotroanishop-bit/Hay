<?php
/**
 * Login History Model
 * Tracks user login attempts for security monitoring
 */

class LoginHistory extends BaseModel
{
    protected string $table = 'login_history';
    protected array $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'location',
        'success',
        'created_at'
    ];

    /**
     * Create a new login history record
     */
    public function createRecord(int $userId, string $ip, ?string $userAgent, bool $success, ?string $location = null): int
    {
        return $this->create([
            'user_id' => $userId,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'location' => $location,
            'success' => $success ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get login history for a user with pagination
     */
    public function getByUser(int $userId, int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get recent login attempts (last 24 hours)
     */
    public function getRecent(int $userId, int $hours = 24): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                AND created_at >= DATE_SUB(NOW(), INTERVAL :hours HOUR)
                ORDER BY created_at DESC";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':hours', $hours, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Count total login history records for a user
     */
    public function countByUser(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }

    /**
     * Get failed login attempts in the last N minutes
     */
    public function getRecentFailedAttempts(int $userId, int $minutes = 15): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE user_id = :user_id 
                AND success = 0
                AND created_at >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':minutes', $minutes, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Parse user agent to get device info
     */
    public function parseUserAgent(?string $userAgent): array
    {
        if (empty($userAgent)) {
            return ['device' => 'Unknown', 'browser' => 'Unknown', 'os' => 'Unknown'];
        }

        $browser = 'Unknown';
        $os = 'Unknown';
        $device = 'Desktop';

        // Detect browser
        if (preg_match('/MSIE|Trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = 'Opera';
        }

        // Detect OS
        if (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Mac/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
            $device = 'Mobile';
        } elseif (preg_match('/iPhone|iPad/i', $userAgent)) {
            $os = 'iOS';
            $device = preg_match('/iPad/i', $userAgent) ? 'Tablet' : 'Mobile';
        }

        // Detect mobile
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent)) {
            $device = 'Mobile';
        } elseif (preg_match('/Tablet|iPad/i', $userAgent)) {
            $device = 'Tablet';
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'os' => $os
        ];
    }
}
