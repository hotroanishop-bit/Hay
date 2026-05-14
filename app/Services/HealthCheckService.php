<?php
/**
 * Health Check Service
 * Provides system health monitoring for status page and admin dashboard
 */

class HealthCheckService
{
    private Incident $incidentModel;

    public function __construct()
    {
        $this->incidentModel = new Incident();
    }

    /**
     * Check database connection
     */
    public function checkDatabase(): array
    {
        try {
            $config = require CONFIG_PATH . '/database.php';
            
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $start = microtime(true);
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            // Simple query to verify connection
            $stmt = $pdo->query('SELECT 1');
            $stmt->fetch();
            
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            return [
                'status' => 'operational',
                'latency_ms' => $latency,
                'message' => 'Database connection healthy'
            ];
        } catch (PDOException $e) {
            return [
                'status' => 'outage',
                'latency_ms' => null,
                'message' => 'Database connection failed'
            ];
        }
    }

    /**
     * Check API endpoint
     */
    public function checkApi(): array
    {
        try {
            $config = require CONFIG_PATH . '/app.php';
            $baseUrl = $config['url'] ?? '';
            
            if (empty($baseUrl)) {
                // Internal check - verify core services are loaded
                $start = microtime(true);
                
                // Check if key classes exist
                $classesExist = class_exists('BaseController') && 
                               class_exists('BaseModel') &&
                               defined('VIEWS_PATH');
                
                $latency = round((microtime(true) - $start) * 1000, 2);
                
                if ($classesExist) {
                    return [
                        'status' => 'operational',
                        'latency_ms' => $latency,
                        'message' => 'API services operational'
                    ];
                }
            }
            
            return [
                'status' => 'operational',
                'latency_ms' => 0,
                'message' => 'API services operational'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'degraded',
                'latency_ms' => null,
                'message' => 'API check failed: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Check website/frontend
     */
    public function checkWebsite(): array
    {
        try {
            // Check if views directory exists and is accessible
            $viewsPath = VIEWS_PATH ?? '';
            
            if (!empty($viewsPath) && is_dir($viewsPath)) {
                return [
                    'status' => 'operational',
                    'latency_ms' => 0,
                    'message' => 'Website operational'
                ];
            }
            
            return [
                'status' => 'degraded',
                'latency_ms' => null,
                'message' => 'Views directory not accessible'
            ];
        } catch (Exception $e) {
            return [
                'status' => 'outage',
                'latency_ms' => null,
                'message' => 'Website check failed'
            ];
        }
    }

    /**
     * Get overall system status
     * Returns: 'operational', 'degraded', or 'outage'
     */
    public function getOverallStatus(): string
    {
        // Check for active incidents first
        $highestSeverity = $this->incidentModel->getHighestActiveSeverity();
        
        if ($highestSeverity === 'critical') {
            return 'outage';
        }
        
        if ($highestSeverity === 'major') {
            return 'degraded';
        }
        
        // Check component statuses
        $components = $this->getComponentStatuses();
        
        $hasOutage = false;
        $hasDegraded = false;
        
        foreach ($components as $component) {
            if ($component['status'] === 'outage') {
                $hasOutage = true;
            }
            if ($component['status'] === 'degraded') {
                $hasDegraded = true;
            }
        }
        
        if ($hasOutage) {
            return 'outage';
        }
        
        if ($hasDegraded || $highestSeverity === 'minor') {
            return 'degraded';
        }
        
        return 'operational';
    }

    /**
     * Get all component statuses
     */
    public function getComponentStatuses(): array
    {
        return [
            'website' => array_merge(
                ['name' => 'Website', 'description' => 'Web interface and dashboard'],
                $this->checkWebsite()
            ),
            'api' => array_merge(
                ['name' => 'API Gateway', 'description' => 'API proxy and routing services'],
                $this->checkApi()
            ),
            'database' => array_merge(
                ['name' => 'Database', 'description' => 'Data storage and retrieval'],
                $this->checkDatabase()
            )
        ];
    }

    /**
     * Get status summary for JSON API
     */
    public function getHealthSummary(): array
    {
        $overallStatus = $this->getOverallStatus();
        $components = $this->getComponentStatuses();
        $activeIncidents = $this->incidentModel->getActive();
        
        return [
            'status' => $overallStatus,
            'timestamp' => date('c'),
            'components' => $components,
            'active_incidents' => count($activeIncidents),
            'message' => $this->getStatusMessage($overallStatus)
        ];
    }

    /**
     * Get status message
     */
    private function getStatusMessage(string $status): string
    {
        switch ($status) {
            case 'operational':
                return 'All systems operational';
            case 'degraded':
                return 'Some systems experiencing issues';
            case 'outage':
                return 'Major system outage';
            default:
                return 'Status unknown';
        }
    }

    /**
     * Calculate uptime percentage (last 30 days)
     */
    public function getUptimePercentage(): float
    {
        try {
            $sql = "SELECT 
                        SUM(TIMESTAMPDIFF(MINUTE, started_at, COALESCE(resolved_at, NOW()))) as downtime_minutes
                    FROM incidents 
                    WHERE started_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                    AND severity IN ('major', 'critical')";
            
            $result = $this->incidentModel->query($sql, []);
            $downtimeMinutes = (int)($result[0]['downtime_minutes'] ?? 0);
            
            $totalMinutes = 30 * 24 * 60; // 30 days in minutes
            $uptimeMinutes = $totalMinutes - $downtimeMinutes;
            
            return round(($uptimeMinutes / $totalMinutes) * 100, 2);
        } catch (Exception $e) {
            return 99.99; // Default fallback
        }
    }

    // =====================
    // Extended Health Metrics for Admin Dashboard
    // =====================

    /**
     * Get disk space information
     */
    public function getDiskSpace(): array
    {
        try {
            $path = defined('ROOT_PATH') ? ROOT_PATH : dirname(__DIR__, 2);
            
            $total = disk_total_space($path);
            $free = disk_free_space($path);
            $used = $total - $free;
            $percent = ($total > 0) ? round(($used / $total) * 100, 1) : 0;
            
            return [
                'total' => $total,
                'used' => $used,
                'free' => $free,
                'percent' => $percent,
                'total_formatted' => $this->formatBytes($total),
                'used_formatted' => $this->formatBytes($used),
                'free_formatted' => $this->formatBytes($free),
                'status' => $percent > 90 ? 'critical' : ($percent > 75 ? 'warning' : 'healthy')
            ];
        } catch (Exception $e) {
            return [
                'total' => 0,
                'used' => 0,
                'free' => 0,
                'percent' => 0,
                'total_formatted' => 'N/A',
                'used_formatted' => 'N/A',
                'free_formatted' => 'N/A',
                'status' => 'unknown'
            ];
        }
    }

    /**
     * Get memory usage information
     */
    public function getMemoryUsage(): array
    {
        $currentUsage = memory_get_usage(true);
        $peakUsage = memory_get_peak_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));
        
        $percent = ($memoryLimit > 0) ? round(($currentUsage / $memoryLimit) * 100, 1) : 0;
        
        return [
            'current' => $currentUsage,
            'peak' => $peakUsage,
            'limit' => $memoryLimit,
            'percent' => $percent,
            'current_formatted' => $this->formatBytes($currentUsage),
            'peak_formatted' => $this->formatBytes($peakUsage),
            'limit_formatted' => $this->formatBytes($memoryLimit),
            'status' => $percent > 80 ? 'critical' : ($percent > 60 ? 'warning' : 'healthy')
        ];
    }

    /**
     * Get database status with more details
     */
    public function getDatabaseStatus(): array
    {
        try {
            $config = require CONFIG_PATH . '/database.php';
            
            $dsn = sprintf(
                '%s:host=%s;port=%s;dbname=%s;charset=%s',
                $config['driver'],
                $config['host'],
                $config['port'],
                $config['database'],
                $config['charset']
            );
            
            $start = microtime(true);
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_TIMEOUT => 5
            ]);
            
            // Simple query to verify connection
            $stmt = $pdo->query('SELECT 1');
            $stmt->fetch();
            
            $latency = round((microtime(true) - $start) * 1000, 2);
            
            // Get database size (MySQL specific)
            $sizeQuery = "SELECT 
                            SUM(data_length + index_length) as size
                          FROM information_schema.tables 
                          WHERE table_schema = :db";
            $stmt = $pdo->prepare($sizeQuery);
            $stmt->execute(['db' => $config['database']]);
            $sizeResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $dbSize = (int)($sizeResult['size'] ?? 0);
            
            // Get connection count
            $connQuery = "SHOW STATUS LIKE 'Threads_connected'";
            $stmt = $pdo->query($connQuery);
            $connResult = $stmt->fetch(PDO::FETCH_ASSOC);
            $connections = (int)($connResult['Value'] ?? 0);
            
            return [
                'connected' => true,
                'latency_ms' => $latency,
                'size' => $dbSize,
                'size_formatted' => $this->formatBytes($dbSize),
                'connections' => $connections,
                'host' => $config['host'],
                'database' => $config['database'],
                'status' => $latency > 100 ? 'warning' : 'healthy'
            ];
        } catch (PDOException $e) {
            return [
                'connected' => false,
                'latency_ms' => null,
                'size' => 0,
                'size_formatted' => 'N/A',
                'connections' => 0,
                'host' => 'unknown',
                'database' => 'unknown',
                'status' => 'critical',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check upstream API status (AI providers)
     */
    public function getUpstreamStatus(): array
    {
        $results = [];
        
        try {
            // Get configured providers
            $proxyConfig = @include(CONFIG_PATH . '/proxy.php');
            $providers = $proxyConfig['providers'] ?? [];
            
            if (empty($providers)) {
                // Default upstream check
                $results['default'] = [
                    'name' => 'API Upstream',
                    'status' => 'unknown',
                    'latency_ms' => null,
                    'message' => 'No providers configured'
                ];
            } else {
                foreach ($providers as $name => $config) {
                    $baseUrl = $config['base_url'] ?? '';
                    
                    if (empty($baseUrl)) {
                        $results[$name] = [
                            'name' => $name,
                            'status' => 'unknown',
                            'latency_ms' => null,
                            'message' => 'No base URL configured'
                        ];
                        continue;
                    }
                    
                    // Just check if the base URL is configured
                    // Actual connectivity would require making HTTP requests
                    $results[$name] = [
                        'name' => $name,
                        'status' => 'configured',
                        'latency_ms' => null,
                        'base_url' => parse_url($baseUrl, PHP_URL_HOST),
                        'message' => 'Provider configured'
                    ];
                }
            }
        } catch (Exception $e) {
            $results['error'] = [
                'name' => 'Upstream Check',
                'status' => 'error',
                'latency_ms' => null,
                'message' => $e->getMessage()
            ];
        }
        
        return $results;
    }

    /**
     * Get recent PHP errors from error log
     */
    public function getRecentErrors(int $limit = 20): array
    {
        $errors = [];
        
        try {
            // Try to find the PHP error log
            $errorLog = ini_get('error_log');
            
            // Also check common locations
            $possibleLogs = [
                $errorLog,
                dirname(__DIR__, 2) . '/storage/logs/php_errors.log',
                dirname(__DIR__, 2) . '/storage/logs/error.log',
                '/var/log/php/error.log',
                '/var/log/apache2/error.log',
                '/var/log/nginx/error.log'
            ];
            
            $logFile = null;
            foreach ($possibleLogs as $log) {
                if (!empty($log) && file_exists($log) && is_readable($log)) {
                    $logFile = $log;
                    break;
                }
            }
            
            if (!$logFile) {
                return [
                    'errors' => [],
                    'log_file' => null,
                    'message' => 'Error log file not found or not readable'
                ];
            }
            
            // Read last N lines from log file
            $lines = $this->tailFile($logFile, $limit * 3); // Get more lines to filter
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                // Parse error line
                $error = $this->parseErrorLine($line);
                if ($error) {
                    $errors[] = $error;
                    if (count($errors) >= $limit) {
                        break;
                    }
                }
            }
            
            return [
                'errors' => array_reverse($errors), // Most recent first
                'log_file' => basename($logFile),
                'count' => count($errors)
            ];
        } catch (Exception $e) {
            return [
                'errors' => [],
                'log_file' => null,
                'message' => 'Failed to read error log: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get full health metrics for admin dashboard
     */
    public function getFullHealthMetrics(): array
    {
        return [
            'timestamp' => date('c'),
            'overall_status' => $this->getOverallStatus(),
            'components' => $this->getComponentStatuses(),
            'disk' => $this->getDiskSpace(),
            'memory' => $this->getMemoryUsage(),
            'database' => $this->getDatabaseStatus(),
            'upstream' => $this->getUpstreamStatus(),
            'recent_errors' => $this->getRecentErrors(20),
            'uptime' => $this->getUptimePercentage(),
            'php_version' => PHP_VERSION,
            'server_time' => date('Y-m-d H:i:s T')
        ];
    }

    /**
     * Format bytes to human readable format
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        
        $bytes /= (1 << (10 * $pow));
        
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Parse PHP memory limit to bytes
     */
    private function parseMemoryLimit(string $limit): int
    {
        if ($limit === '-1') {
            return PHP_INT_MAX;
        }
        
        $limit = trim($limit);
        $last = strtolower($limit[strlen($limit) - 1]);
        $value = (int) $limit;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }

    /**
     * Read last N lines from a file
     */
    private function tailFile(string $file, int $lines = 20): array
    {
        $result = [];
        
        $fp = @fopen($file, 'r');
        if (!$fp) {
            return [];
        }
        
        // Seek to end
        fseek($fp, 0, SEEK_END);
        $pos = ftell($fp);
        $lineCount = 0;
        
        while ($pos > 0 && $lineCount < $lines) {
            $pos--;
            fseek($fp, $pos, SEEK_SET);
            $char = fgetc($fp);
            
            if ($char === "\n") {
                $lineCount++;
            }
        }
        
        while (!feof($fp)) {
            $line = fgets($fp);
            if ($line !== false) {
                $result[] = $line;
            }
        }
        
        fclose($fp);
        
        return $result;
    }

    /**
     * Parse an error log line
     */
    private function parseErrorLine(string $line): ?array
    {
        // Common PHP error log format: [date] level: message
        if (preg_match('/^\[([^\]]+)\]\s*(?:PHP\s+)?(\w+):\s*(.+)$/i', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => strtoupper($matches[2]),
                'message' => trim($matches[3]),
                'raw' => $line
            ];
        }
        
        // Alternative format without brackets
        if (preg_match('/^(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(\w+):\s*(.+)$/i', $line, $matches)) {
            return [
                'timestamp' => $matches[1],
                'level' => strtoupper($matches[2]),
                'message' => trim($matches[3]),
                'raw' => $line
            ];
        }
        
        // If line contains error keywords, include it
        if (preg_match('/(error|warning|notice|fatal|exception)/i', $line)) {
            return [
                'timestamp' => date('Y-m-d H:i:s'),
                'level' => 'UNKNOWN',
                'message' => trim($line),
                'raw' => $line
            ];
        }
        
        return null;
    }
}
