<?php
/**
 * Notification Model
 * Handles user notifications (null user_id = broadcast to all)
 */

class Notification extends BaseModel
{
    protected string $table = 'notifications';
    
    protected array $fillable = [
        'user_id',
        'title',
        'message',
        'type',
        'is_read',
        'created_at'
    ];

    /**
     * Get count of unread notifications for a user (including broadcasts)
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        
        return (int) ($result['count'] ?? 0);
    }

    /**
     * Mark a notification as read
     */
    public function markAsRead(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_read = 1 WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    /**
     * Mark all unread notifications as read for a user
     */
    public function markAllRead(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET is_read = 1 
                WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 0";
        return $this->execute($sql, ['user_id' => $userId]);
    }

    /**
     * Find notifications for a user (optionally including global broadcasts)
     */
    public function findByUser(int $userId, bool $includeGlobal = true): array
    {
        if ($includeGlobal) {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = :user_id OR user_id IS NULL 
                    ORDER BY created_at DESC";
        } else {
            $sql = "SELECT * FROM {$this->table} 
                    WHERE user_id = :user_id 
                    ORDER BY created_at DESC";
        }
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Create a broadcast notification (sent to all users)
     */
    public function createBroadcast(string $title, string $message, string $type = 'info'): int
    {
        $validTypes = ['info', 'warning', 'success', 'error'];
        if (!in_array($type, $validTypes)) {
            $type = 'info';
        }
        
        return $this->create([
            'user_id' => null,
            'title' => $title,
            'message' => $message,
            'type' => $type,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get paginated notifications for a user with filter
     */
    public function getForUserPaginated(int $userId, int $page = 1, int $perPage = 20, string $filter = 'all'): array
    {
        $offset = ($page - 1) * $perPage;
        $params = ['user_id' => $userId];
        $whereClause = "(user_id = :user_id OR user_id IS NULL)";
        
        // Apply filter
        if ($filter === 'unread') {
            $whereClause .= " AND is_read = 0";
        } elseif (in_array($filter, ['info', 'success', 'warning', 'error'])) {
            $whereClause .= " AND type = :type";
            $params['type'] = $filter;
        }
        
        // Count total
        $countSql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$whereClause}";
        $stmt = $this->db()->prepare($countSql);
        $stmt->execute($params);
        $total = (int) ($stmt->fetch()['total'] ?? 0);
        
        // Get notifications
        $sql = "SELECT * FROM {$this->table} 
                WHERE {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT {$perPage} OFFSET {$offset}";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute($params);
        $notifications = $stmt->fetchAll();
        
        $hasMore = ($offset + count($notifications)) < $total;
        
        return [
            'notifications' => $notifications,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'has_more' => $hasMore
        ];
    }

    /**
     * Get notification counts by type for a user
     */
    public function getCounts(int $userId): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                    SUM(CASE WHEN type = 'info' THEN 1 ELSE 0 END) as info,
                    SUM(CASE WHEN type = 'success' THEN 1 ELSE 0 END) as success,
                    SUM(CASE WHEN type = 'warning' THEN 1 ELSE 0 END) as warning,
                    SUM(CASE WHEN type = 'error' THEN 1 ELSE 0 END) as error
                FROM {$this->table} 
                WHERE user_id = :user_id OR user_id IS NULL";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        $result = $stmt->fetch();
        
        return [
            'total' => (int) ($result['total'] ?? 0),
            'unread' => (int) ($result['unread'] ?? 0),
            'info' => (int) ($result['info'] ?? 0),
            'success' => (int) ($result['success'] ?? 0),
            'warning' => (int) ($result['warning'] ?? 0),
            'error' => (int) ($result['error'] ?? 0)
        ];
    }

    /**
     * Delete all read notifications for a user
     */
    public function deleteRead(int $userId): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE (user_id = :user_id OR user_id IS NULL) AND is_read = 1";
        return $this->execute($sql, ['user_id' => $userId]);
    }
}
