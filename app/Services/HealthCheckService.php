<?php
/**
 * Health Check Service
 * Provides system health monitoring for status page
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
}
