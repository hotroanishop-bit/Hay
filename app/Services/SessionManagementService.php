<?php
/**
 * Session Management Service
 * Handles session creation, validation, and management for security tracking
 */

class SessionManagementService
{
    private UserSession $sessionModel;
    private LoginHistory $loginHistoryModel;

    public function __construct()
    {
        $this->sessionModel = new UserSession();
        $this->loginHistoryModel = new LoginHistory();
    }

    /**
     * Create a new session for a user
     * @return string Session token
     */
    public function createSession(int $userId, string $ip, ?string $userAgent): string
    {
        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        
        // Create session record
        $this->sessionModel->createSession($userId, $token, $ip, $userAgent);
        
        return $token;
    }

    /**
     * Validate a session token
     */
    public function validateSession(string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        
        return $this->sessionModel->isValidSession($token);
    }

    /**
     * Refresh session last active time
     */
    public function refreshSession(string $token): bool
    {
        return $this->sessionModel->updateLastActive($token);
    }

    /**
     * Log a login attempt
     */
    public function logLogin(int $userId, string $ip, ?string $userAgent, bool $success, ?string $location = null): int
    {
        return $this->loginHistoryModel->createRecord($userId, $ip, $userAgent, $success, $location);
    }

    /**
     * Get active sessions for a user
     */
    public function getActiveSessions(int $userId): array
    {
        $sessions = $this->sessionModel->getByUser($userId);
        
        // Enrich with parsed user agent
        foreach ($sessions as &$session) {
            $session['device_info'] = $this->sessionModel->parseUserAgent($session['user_agent'] ?? null);
        }
        
        return $sessions;
    }

    /**
     * Get login history for a user
     */
    public function getLoginHistory(int $userId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $history = $this->loginHistoryModel->getByUser($userId, $perPage, $offset);
        
        // Enrich with parsed user agent
        foreach ($history as &$record) {
            $record['device_info'] = $this->loginHistoryModel->parseUserAgent($record['user_agent'] ?? null);
        }
        
        return $history;
    }

    /**
     * Count login history entries
     */
    public function countLoginHistory(int $userId): int
    {
        return $this->loginHistoryModel->countByUser($userId);
    }

    /**
     * Terminate a specific session
     */
    public function terminateSession(int $sessionId, int $userId): bool
    {
        return $this->sessionModel->deleteUserSession($sessionId, $userId);
    }

    /**
     * Terminate all sessions except current
     */
    public function terminateAllOtherSessions(int $userId, string $currentToken): int
    {
        return $this->sessionModel->deleteAllExcept($userId, $currentToken);
    }

    /**
     * Terminate all sessions for a user (used for logout everywhere)
     */
    public function terminateAllSessions(int $userId): int
    {
        return $this->sessionModel->deleteAllByUser($userId);
    }

    /**
     * Get session by token
     */
    public function getSessionByToken(string $token): ?array
    {
        return $this->sessionModel->getByToken($token);
    }

    /**
     * Count active sessions
     */
    public function countActiveSessions(int $userId): int
    {
        return $this->sessionModel->countActiveSessions($userId);
    }

    /**
     * Get recent failed login attempts
     */
    public function getRecentFailedAttempts(int $userId, int $minutes = 15): int
    {
        return $this->loginHistoryModel->getRecentFailedAttempts($userId, $minutes);
    }

    /**
     * Clean up old sessions
     */
    public function cleanupOldSessions(int $daysOld = 30): int
    {
        return $this->sessionModel->cleanupOldSessions($daysOld);
    }
}
