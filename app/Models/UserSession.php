<?php
/**
 * User Session Model
 * Manages persistent user sessions for multi-device login tracking
 */

class UserSession extends BaseModel
{
    protected string $table = 'user_sessions';
    protected array $fillable = [
        'user_id',
        'token',
        'ip_address',
        'user_agent',
        'last_active',
        'created_at'
    ];

    /**
     * Create a new session
     */
    public function createSession(int $userId, string $token, string $ip, ?string $userAgent): int
    {
        return $this->create([
            'user_id' => $userId,
            'token' => $token,
            'ip_address' => $ip,
            'user_agent' => $userAgent,
            'last_active' => date('Y-m-d H:i:s'),
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all sessions for a user
     */
    public function getByUser(int $userId): array
    {
        return $this->findAll(
            ['user_id' => $userId],
            'last_active DESC'
        );
    }

    /**
     * Get session by token
     */
    public function getByToken(string $token): ?array
    {
        return $this->findBy(['token' => $token]);
    }

    /**
     * Update last active timestamp
     */
    public function updateLastActive(string $token): bool
    {
        $sql = "UPDATE {$this->table} SET last_active = NOW() WHERE token = :token";
        return $this->execute($sql, ['token' => $token]);
    }

    /**
     * Delete a session by ID (for termination)
     */
    public function deleteSession(int $sessionId): bool
    {
        return $this->delete($sessionId);
    }

    /**
     * Delete a session by ID only if it belongs to the user
     */
    public function deleteUserSession(int $sessionId, int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id AND user_id = :user_id";
        return $this->execute($sql, ['id' => $sessionId, 'user_id' => $userId]);
    }

    /**
     * Delete all sessions except the specified token
     */
    public function deleteAllExcept(int $userId, string $exceptToken): int
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id AND token != :token";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId, 'token' => $exceptToken]);
        return $stmt->rowCount();
    }

    /**
     * Delete all sessions for a user
     */
    public function deleteAllByUser(int $userId): int
    {
        $sql = "DELETE FROM {$this->table} WHERE user_id = :user_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->rowCount();
    }

    /**
     * Check if a session is valid (exists and not expired)
     */
    public function isValidSession(string $token, int $maxInactiveMinutes = 43200): bool
    {
        $sql = "SELECT id FROM {$this->table} 
                WHERE token = :token 
                AND last_active >= DATE_SUB(NOW(), INTERVAL :minutes MINUTE)";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':token', $token, PDO::PARAM_STR);
        $stmt->bindValue(':minutes', $maxInactiveMinutes, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetch() !== false;
    }

    /**
     * Count active sessions for a user
     */
    public function countActiveSessions(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }

    /**
     * Clean up old sessions (older than 30 days)
     */
    public function cleanupOldSessions(int $daysOld = 30): int
    {
        $sql = "DELETE FROM {$this->table} WHERE last_active < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':days', $daysOld, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->rowCount();
    }

    /**
     * Parse user agent for session display
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
        } elseif (preg_match('/Edg/i', $userAgent)) {
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
        if (preg_match('/Windows NT 10/i', $userAgent)) {
            $os = 'Windows 10/11';
        } elseif (preg_match('/Windows/i', $userAgent)) {
            $os = 'Windows';
        } elseif (preg_match('/Macintosh/i', $userAgent)) {
            $os = 'macOS';
        } elseif (preg_match('/Linux/i', $userAgent)) {
            $os = 'Linux';
        } elseif (preg_match('/Android/i', $userAgent)) {
            $os = 'Android';
            $device = 'Mobile';
        } elseif (preg_match('/iPhone/i', $userAgent)) {
            $os = 'iOS';
            $device = 'Mobile';
        } elseif (preg_match('/iPad/i', $userAgent)) {
            $os = 'iPadOS';
            $device = 'Tablet';
        }

        // Detect mobile
        if (preg_match('/Mobile|Android|iPhone/i', $userAgent) && $device === 'Desktop') {
            $device = 'Mobile';
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'os' => $os
        ];
    }
}
