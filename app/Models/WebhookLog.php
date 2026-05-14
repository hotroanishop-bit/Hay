<?php
/**
 * WebhookLog Model
 * Handles webhook delivery logs
 */

class WebhookLog extends BaseModel
{
    protected string $table = 'webhook_logs';
    
    protected array $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_code',
        'response_body',
        'attempts',
        'created_at'
    ];

    /**
     * Create a new webhook log entry
     */
    public function createLog(int $webhookId, string $event, array $payload, ?int $responseCode = null, ?string $responseBody = null, int $attempts = 1): int
    {
        return $this->create([
            'webhook_id' => $webhookId,
            'event' => $event,
            'payload' => json_encode($payload),
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'attempts' => $attempts,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get logs for a specific webhook with limit
     */
    public function getByWebhook(int $webhookId, int $limit = 50): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE webhook_id = :webhook_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':webhook_id', $webhookId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        $logs = $stmt->fetchAll();
        
        // Decode payload JSON
        foreach ($logs as &$log) {
            if (is_string($log['payload'])) {
                $log['payload'] = json_decode($log['payload'], true) ?? [];
            }
        }
        
        return $logs;
    }

    /**
     * Get recent logs for a webhook (last 10)
     */
    public function getRecentLogs(int $webhookId): array
    {
        return $this->getByWebhook($webhookId, 10);
    }

    /**
     * Get logs with pagination
     */
    public function getLogsPaginated(int $webhookId, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE webhook_id = :webhook_id";
        $stmt = $this->db()->prepare($countSql);
        $stmt->execute(['webhook_id' => $webhookId]);
        $total = (int) ($stmt->fetch()['total'] ?? 0);
        
        // Get logs
        $sql = "SELECT * FROM {$this->table} 
                WHERE webhook_id = :webhook_id 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['webhook_id' => $webhookId]);
        $logs = $stmt->fetchAll();
        
        // Decode payload JSON
        foreach ($logs as &$log) {
            if (is_string($log['payload'])) {
                $log['payload'] = json_decode($log['payload'], true) ?? [];
            }
        }
        
        return [
            'logs' => $logs,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => ($offset + count($logs)) < $total
        ];
    }

    /**
     * Update log with retry information
     */
    public function updateAttempt(int $id, int $responseCode, ?string $responseBody, int $attempts): bool
    {
        $sql = "UPDATE {$this->table} 
                SET response_code = :response_code, 
                    response_body = :response_body, 
                    attempts = :attempts 
                WHERE id = :id";
        return $this->execute($sql, [
            'id' => $id,
            'response_code' => $responseCode,
            'response_body' => $responseBody,
            'attempts' => $attempts
        ]);
    }

    /**
     * Get success/failure statistics for a webhook
     */
    public function getStats(int $webhookId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN response_code >= 200 AND response_code < 300 THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN response_code IS NULL OR response_code < 200 OR response_code >= 300 THEN 1 ELSE 0 END) as failed,
                    AVG(attempts) as avg_attempts
                FROM {$this->table} 
                WHERE webhook_id = :webhook_id";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['webhook_id' => $webhookId]);
        $result = $stmt->fetch();
        
        return [
            'total' => (int) ($result['total'] ?? 0),
            'success' => (int) ($result['success'] ?? 0),
            'failed' => (int) ($result['failed'] ?? 0),
            'avg_attempts' => round((float) ($result['avg_attempts'] ?? 1), 2)
        ];
    }

    /**
     * Delete old logs (cleanup)
     */
    public function deleteOldLogs(int $days = 30): int
    {
        $sql = "DELETE FROM {$this->table} WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['days' => $days]);
        
        return $stmt->rowCount();
    }

    /**
     * Get event counts for a webhook
     */
    public function getEventCounts(int $webhookId): array
    {
        $sql = "SELECT event, COUNT(*) as count 
                FROM {$this->table} 
                WHERE webhook_id = :webhook_id 
                GROUP BY event 
                ORDER BY count DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['webhook_id' => $webhookId]);
        
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
