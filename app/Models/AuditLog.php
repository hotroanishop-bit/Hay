<?php
/**
 * AuditLog Model
 * Handles admin action logging for security and compliance
 */

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';
    
    protected array $fillable = [
        'admin_id',
        'action',
        'target_type',
        'target_id',
        'old_value',
        'new_value',
        'ip_address',
        'created_at'
    ];

    /**
     * Log an admin action
     */
    public function logAction(
        int $adminId,
        string $action,
        ?string $targetType,
        ?int $targetId,
        $oldValue,
        $newValue,
        ?string $ip
    ): int {
        $data = [
            'admin_id' => $adminId,
            'action' => $action,
            'target_type' => $targetType,
            'target_id' => $targetId,
            'old_value' => $oldValue !== null ? json_encode($oldValue) : null,
            'new_value' => $newValue !== null ? json_encode($newValue) : null,
            'ip_address' => $ip ?? $this->getClientIP(),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($data);
    }

    /**
     * Get audit logs by admin
     */
    public function getByAdmin(int $adminId, int $limit = 50): array
    {
        $sql = "SELECT al.*, u.name as admin_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.admin_id = u.id 
                WHERE al.admin_id = :admin_id 
                ORDER BY al.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':admin_id', $adminId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->decodeJsonFields($stmt->fetchAll());
    }

    /**
     * Get audit logs by target
     */
    public function getByTarget(string $type, int $id): array
    {
        $sql = "SELECT al.*, u.name as admin_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.admin_id = u.id 
                WHERE al.target_type = :type AND al.target_id = :id 
                ORDER BY al.created_at DESC";
        
        $results = $this->query($sql, ['type' => $type, 'id' => $id]);
        return $this->decodeJsonFields($results);
    }

    /**
     * Get recent audit logs
     */
    public function getRecent(int $limit = 100): array
    {
        $sql = "SELECT al.*, u.name as admin_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.admin_id = u.id 
                ORDER BY al.created_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $this->decodeJsonFields($stmt->fetchAll());
    }

    /**
     * Search audit logs with filters and pagination
     */
    public function search(array $filters, int $page = 1, int $perPage = 50): array
    {
        $where = [];
        $params = [];
        
        if (!empty($filters['admin_id'])) {
            $where[] = "al.admin_id = :admin_id";
            $params['admin_id'] = $filters['admin_id'];
        }
        
        if (!empty($filters['action'])) {
            $where[] = "al.action LIKE :action";
            $params['action'] = '%' . $filters['action'] . '%';
        }
        
        if (!empty($filters['target_type'])) {
            $where[] = "al.target_type = :target_type";
            $params['target_type'] = $filters['target_type'];
        }
        
        if (!empty($filters['date_from'])) {
            $where[] = "al.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $where[] = "al.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'];
        }
        
        if (!empty($filters['ip_address'])) {
            $where[] = "al.ip_address = :ip_address";
            $params['ip_address'] = $filters['ip_address'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} al {$whereClause}";
        $countResult = $this->query($countSql, $params);
        $total = (int) ($countResult[0]['total'] ?? 0);
        
        // Get paginated results
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT al.*, u.name as admin_name 
                FROM {$this->table} al 
                LEFT JOIN users u ON al.admin_id = u.id 
                {$whereClause} 
                ORDER BY al.created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        
        $data = $this->query($sql, $params);
        
        return [
            'data' => $this->decodeJsonFields($data),
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Decode JSON fields in results
     */
    private function decodeJsonFields(array $results): array
    {
        foreach ($results as &$row) {
            if (isset($row['old_value']) && $row['old_value'] !== null) {
                $row['old_value'] = json_decode($row['old_value'], true);
            }
            if (isset($row['new_value']) && $row['new_value'] !== null) {
                $row['new_value'] = json_decode($row['new_value'], true);
            }
        }
        return $results;
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
