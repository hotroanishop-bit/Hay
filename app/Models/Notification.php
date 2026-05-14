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
}
