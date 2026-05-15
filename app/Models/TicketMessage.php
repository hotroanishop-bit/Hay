<?php
/**
 * Ticket Message Model
 * Handles conversation messages within support tickets
 */

class TicketMessage extends BaseModel
{
    protected string $table = 'ticket_messages';
    
    protected array $fillable = [
        'ticket_id',
        'sender_type',
        'sender_id',
        'message',
        'attachments',
        'is_internal',
        'created_at'
    ];

    // Sender types
    const SENDER_USER = 'user';
    const SENDER_ADMIN = 'admin';

    /**
     * Create a new message
     */
    public function createMessage(int $ticketId, int $senderId, string $senderType, string $message, array $attachments = [], bool $isInternal = false): int
    {
        return $this->create([
            'ticket_id' => $ticketId,
            'sender_id' => $senderId,
            'sender_type' => $senderType,
            'message' => $message,
            'attachments' => !empty($attachments) ? json_encode($attachments) : null,
            'is_internal' => $isInternal ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get all messages for a ticket
     */
    public function getByTicket(int $ticketId, bool $includeInternal = false): array
    {
        $params = ['ticket_id' => $ticketId];
        
        $sql = "SELECT m.*, u.name as sender_name, u.email as sender_email, u.avatar_url as sender_avatar
                FROM {$this->table} m
                LEFT JOIN users u ON m.sender_id = u.id
                WHERE m.ticket_id = :ticket_id";
        
        if (!$includeInternal) {
            $sql .= " AND m.is_internal = 0";
        }
        
        $sql .= " ORDER BY m.created_at ASC";
        
        $messages = $this->query($sql, $params);
        
        // Decode attachments JSON
        foreach ($messages as &$msg) {
            if ($msg['attachments']) {
                $msg['attachments'] = json_decode($msg['attachments'], true);
            } else {
                $msg['attachments'] = [];
            }
        }
        
        return $messages;
    }

    /**
     * Get the last message for a ticket
     */
    public function getLastMessage(int $ticketId): ?array
    {
        $sql = "SELECT m.*, u.name as sender_name
                FROM {$this->table} m
                LEFT JOIN users u ON m.sender_id = u.id
                WHERE m.ticket_id = :ticket_id AND m.is_internal = 0
                ORDER BY m.created_at DESC
                LIMIT 1";
        $result = $this->query($sql, ['ticket_id' => $ticketId]);
        return $result[0] ?? null;
    }

    /**
     * Count messages for a ticket
     */
    public function countByTicket(int $ticketId): int
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE ticket_id = :ticket_id AND is_internal = 0";
        $result = $this->query($sql, ['ticket_id' => $ticketId]);
        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Get internal notes for a ticket (admin only)
     */
    public function getInternalNotes(int $ticketId): array
    {
        $sql = "SELECT m.*, u.name as sender_name
                FROM {$this->table} m
                LEFT JOIN users u ON m.sender_id = u.id
                WHERE m.ticket_id = :ticket_id AND m.is_internal = 1
                ORDER BY m.created_at ASC";
        return $this->query($sql, ['ticket_id' => $ticketId]);
    }

    /**
     * Check if user has unread admin replies
     */
    public function hasUnreadAdminReply(int $ticketId, int $userId): bool
    {
        // Get the last user message timestamp
        $sql = "SELECT MAX(created_at) as last_user_msg 
                FROM {$this->table} 
                WHERE ticket_id = :ticket_id 
                AND sender_type = 'user' 
                AND sender_id = :user_id";
        $result = $this->query($sql, ['ticket_id' => $ticketId, 'user_id' => $userId]);
        $lastUserMsg = $result[0]['last_user_msg'] ?? null;
        
        // Check if there are admin messages after the last user message
        $sql = "SELECT COUNT(*) as count 
                FROM {$this->table} 
                WHERE ticket_id = :ticket_id 
                AND sender_type = 'admin' 
                AND is_internal = 0";
        
        if ($lastUserMsg) {
            $sql .= " AND created_at > :last_user_msg";
            $result = $this->query($sql, ['ticket_id' => $ticketId, 'last_user_msg' => $lastUserMsg]);
        } else {
            $result = $this->query($sql, ['ticket_id' => $ticketId]);
        }
        
        return (int) ($result[0]['count'] ?? 0) > 0;
    }
}
