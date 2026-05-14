<?php
/**
 * UsageLog Model
 * Handles API usage tracking and statistics
 */

class UsageLog extends BaseModel
{
    protected string $table = 'usage_logs';
    
    protected array $fillable = [
        'api_key_id',
        'user_id',
        'endpoint',
        'tokens_used',
        'cost',
        'ip_address',
        'response_code',
        'created_at'
    ];

    /**
     * Log API usage
     */
    public function logUsage(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Get usage statistics for a specific API key
     */
    public function getStatsByKey(int $apiKeyId, ?string $startDate = null, ?string $endDate = null): array
    {
        $params = ['api_key_id' => $apiKeyId];
        $dateCondition = '';
        
        if ($startDate) {
            $dateCondition .= ' AND created_at >= :start_date';
            $params['start_date'] = $startDate;
        }
        if ($endDate) {
            $dateCondition .= ' AND created_at <= :end_date';
            $params['end_date'] = $endDate;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COALESCE(SUM(tokens_used), 0) as total_tokens,
                    COALESCE(SUM(cost), 0) as total_cost,
                    AVG(tokens_used) as avg_tokens_per_request
                FROM {$this->table} 
                WHERE api_key_id = :api_key_id{$dateCondition}";
        
        $result = $this->query($sql, $params);
        return $result[0] ?? [];
    }

    /**
     * Get usage statistics for a user across all API keys
     */
    public function getStatsByUser(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $params = ['user_id' => $userId];
        $dateCondition = '';
        
        if ($startDate) {
            $dateCondition .= ' AND created_at >= :start_date';
            $params['start_date'] = $startDate;
        }
        if ($endDate) {
            $dateCondition .= ' AND created_at <= :end_date';
            $params['end_date'] = $endDate;
        }
        
        $sql = "SELECT 
                    COUNT(*) as total_requests,
                    COALESCE(SUM(tokens_used), 0) as total_tokens,
                    COALESCE(SUM(cost), 0) as total_cost,
                    AVG(tokens_used) as avg_tokens_per_request
                FROM {$this->table} 
                WHERE user_id = :user_id{$dateCondition}";
        
        $result = $this->query($sql, $params);
        return $result[0] ?? [];
    }

    /**
     * Get daily usage statistics
     */
    public function getDailyStats(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    DATE(created_at) as date,
                    COUNT(*) as requests,
                    COALESCE(SUM(tokens_used), 0) as tokens,
                    COALESCE(SUM(cost), 0) as cost
                FROM {$this->table} 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                GROUP BY DATE(created_at)
                ORDER BY date DESC";
        
        return $this->query($sql, ['user_id' => $userId, 'days' => $days]);
    }

    /**
     * Get recent usage logs for an API key
     */
    public function getRecentByKey(int $apiKeyId, int $limit = 50): array
    {
        return $this->findAll(['api_key_id' => $apiKeyId], 'created_at DESC', $limit);
    }

    /**
     * Get recent usage logs for a user
     */
    public function getRecentByUser(int $userId, int $limit = 50): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC', $limit);
    }

    /**
     * Get usage by endpoint
     */
    public function getUsageByEndpoint(int $userId): array
    {
        $sql = "SELECT 
                    endpoint,
                    COUNT(*) as requests,
                    COALESCE(SUM(tokens_used), 0) as total_tokens,
                    COALESCE(SUM(cost), 0) as total_cost
                FROM {$this->table} 
                WHERE user_id = :user_id
                GROUP BY endpoint
                ORDER BY requests DESC";
        
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Get hourly usage distribution
     */
    public function getHourlyDistribution(int $userId, int $days = 7): array
    {
        $sql = "SELECT 
                    HOUR(created_at) as hour,
                    COUNT(*) as requests
                FROM {$this->table} 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(NOW(), INTERVAL :days DAY)
                GROUP BY HOUR(created_at)
                ORDER BY hour";
        
        return $this->query($sql, ['user_id' => $userId, 'days' => $days]);
    }
}
