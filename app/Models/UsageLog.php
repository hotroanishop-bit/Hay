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
        'input_tokens',
        'output_tokens',
        'model',
        'response_time_ms',
        'request_id',
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
     * Log detailed API request with all tracking fields
     * 
     * @param array $data Request data including input_tokens, output_tokens, model, response_time_ms
     * @return int Insert ID
     */
    public function logApiRequest(array $data): int
    {
        // Generate request_id if not provided
        if (empty($data['request_id'])) {
            $data['request_id'] = sprintf(
                '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                mt_rand(0, 0xffff),
                mt_rand(0, 0x0fff) | 0x4000,
                mt_rand(0, 0x3fff) | 0x8000,
                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
            );
        }
        
        // Ensure tokens_used is set as sum of input and output if not provided
        if (!isset($data['tokens_used']) && isset($data['input_tokens']) && isset($data['output_tokens'])) {
            $data['tokens_used'] = (int) $data['input_tokens'] + (int) $data['output_tokens'];
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        return $this->create($data);
    }

    /**
     * Get usage aggregated by model for a user
     * 
     * @param int $userId User ID
     * @param string|null $startDate Start date (Y-m-d)
     * @param string|null $endDate End date (Y-m-d)
     * @return array Usage data grouped by model
     */
    public function getUsageByModel(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $params = ['user_id' => $userId];
        $dateCondition = '';
        
        if ($startDate) {
            $dateCondition .= ' AND created_at >= :start_date';
            $params['start_date'] = $startDate . ' 00:00:00';
        }
        if ($endDate) {
            $dateCondition .= ' AND created_at <= :end_date';
            $params['end_date'] = $endDate . ' 23:59:59';
        }
        
        $sql = "SELECT 
                    COALESCE(model, 'unknown') as model,
                    COUNT(*) as total_requests,
                    COALESCE(SUM(tokens_used), 0) as total_tokens,
                    COALESCE(SUM(input_tokens), 0) as total_input_tokens,
                    COALESCE(SUM(output_tokens), 0) as total_output_tokens,
                    COALESCE(SUM(cost), 0) as total_cost,
                    COALESCE(AVG(tokens_used), 0) as avg_tokens_per_request,
                    COALESCE(AVG(response_time_ms), 0) as avg_response_time_ms
                FROM {$this->table} 
                WHERE user_id = :user_id AND model IS NOT NULL{$dateCondition}
                GROUP BY model
                ORDER BY total_requests DESC";
        
        return $this->query($sql, $params);
    }

    /**
     * Get total token usage for a specific day (for rate limiting)
     * 
     * @param int $userId User ID
     * @param string $date Date in Y-m-d format
     * @return int Total tokens used on that day
     */
    public function getDailyTokenUsage(int $userId, string $date): int
    {
        $sql = "SELECT COALESCE(SUM(tokens_used), 0) as total_tokens
                FROM {$this->table} 
                WHERE user_id = :user_id 
                    AND DATE(created_at) = :date";
        
        $result = $this->query($sql, ['user_id' => $userId, 'date' => $date]);
        return (int) ($result[0]['total_tokens'] ?? 0);
    }

    /**
     * Get model statistics for analytics
     * 
     * @param int $userId User ID
     * @param int $days Number of days to look back
     * @return array Model statistics
     */
    public function getModelStats(int $userId, int $days = 30): array
    {
        $sql = "SELECT 
                    COALESCE(model, 'unknown') as model,
                    COUNT(*) as total_requests,
                    COALESCE(SUM(tokens_used), 0) as total_tokens,
                    COALESCE(SUM(input_tokens), 0) as total_input_tokens,
                    COALESCE(SUM(output_tokens), 0) as total_output_tokens,
                    COALESCE(SUM(cost), 0) as total_cost,
                    COALESCE(AVG(tokens_used), 0) as avg_tokens_per_request,
                    COALESCE(AVG(response_time_ms), 0) as avg_response_time_ms,
                    MIN(created_at) as first_used,
                    MAX(created_at) as last_used
                FROM {$this->table} 
                WHERE user_id = :user_id 
                    AND created_at >= DATE_SUB(CURDATE(), INTERVAL :days DAY)
                    AND model IS NOT NULL
                GROUP BY model
                ORDER BY total_requests DESC";
        
        return $this->query($sql, ['user_id' => $userId, 'days' => $days]);
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
