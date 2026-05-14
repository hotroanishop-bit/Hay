<?php
/**
 * Audit Service
 * Handles audit logging and activity tracking
 */

class AuditService
{
    private string $logFile;
    private string $systemLogFile;

    public function __construct()
    {
        $config = require CONFIG_PATH . '/app.php';
        $logDir = dirname(CONFIG_PATH) . '/storage/logs';

        // Ensure log directory exists
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0755, true);
        }

        $this->logFile = $logDir . '/audit.log';
        $this->systemLogFile = $logDir . '/system.log';
    }

    /**
     * Log a user action
     */
    public function log(int $userId, string $action, array $details = []): bool
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'action' => $action,
            'details' => $details,
            'ip_address' => $this->getClientIP(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
        ];

        return $this->writeLog($this->logFile, $entry);
    }

    /**
     * Get audit log entries for a user
     */
    public function getAuditLog(int $userId, int $limit = 100): array
    {
        return $this->readLogs($this->logFile, function ($entry) use ($userId) {
            return isset($entry['user_id']) && $entry['user_id'] === $userId;
        }, $limit);
    }

    /**
     * Get system-wide audit log
     */
    public function getSystemLog(int $limit = 100): array
    {
        return $this->readLogs($this->systemLogFile, null, $limit);
    }

    /**
     * Log a login attempt
     */
    public function logLogin(int $userId, string $ip, bool $success = true): bool
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'action' => $success ? 'login_success' : 'login_failed',
            'details' => [
                'ip_address' => $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            ],
            'ip_address' => $ip,
        ];

        $this->writeLog($this->logFile, $entry);

        // Also log to system log for security monitoring
        $systemEntry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'level' => $success ? 'info' : 'warning',
            'message' => $success ? "User {$userId} logged in" : "Failed login attempt for user {$userId}",
            'context' => ['ip' => $ip],
        ];

        return $this->writeLog($this->systemLogFile, $systemEntry);
    }

    /**
     * Log API key usage
     */
    public function logKeyUsage(int $keyId, array $data): bool
    {
        $entry = [
            'timestamp' => date('Y-m-d H:i:s'),
            'action' => 'api_key_usage',
            'key_id' => $keyId,
            'details' => [
                'endpoint' => $data['endpoint'] ?? 'unknown',
                'tokens_used' => $data['tokens_used'] ?? 0,
                'cost' => $data['cost'] ?? 0,
                'response_code' => $data['response_code'] ?? 200,
            ],
            'ip_address' => $data['ip_address'] ?? $this->getClientIP(),
        ];

        return $this->writeLog($this->logFile, $entry);
    }

    /**
     * Write a log entry to file
     */
    private function writeLog(string $file, array $entry): bool
    {
        $line = json_encode($entry) . PHP_EOL;
        return file_put_contents($file, $line, FILE_APPEND | LOCK_EX) !== false;
    }

    /**
     * Read logs from file with optional filter
     */
    private function readLogs(string $file, ?callable $filter = null, int $limit = 100): array
    {
        if (!file_exists($file)) {
            return [];
        }

        $logs = [];
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        // Read from end of file (most recent first)
        $lines = array_reverse($lines);

        foreach ($lines as $line) {
            $entry = json_decode($line, true);
            if (!$entry) {
                continue;
            }

            if ($filter === null || $filter($entry)) {
                $logs[] = $entry;
                if (count($logs) >= $limit) {
                    break;
                }
            }
        }

        return $logs;
    }

    /**
     * Get client IP address
     */
    private function getClientIP(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                // Handle comma-separated IPs (X-Forwarded-For)
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }
}
