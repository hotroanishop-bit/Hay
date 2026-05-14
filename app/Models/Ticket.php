<?php
/**
 * Ticket Model
 * Handles support ticket management
 */

class Ticket extends BaseModel
{
    protected string $table = 'tickets';
    
    protected array $fillable = [
        'user_id',
        'subject',
        'message',
        'status',
        'priority',
        'admin_reply',
        'created_at',
        'updated_at'
    ];

    // Ticket statuses
    const STATUS_OPEN = 'open';
    const STATUS_PENDING = 'pending';
    const STATUS_CLOSED = 'closed';

    // Ticket priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';

    /**
     * Find all tickets for a user
     */
    public function findByUser(int $userId): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC');
    }

    /**
     * Update ticket status
     */
    public function updateStatus(int $ticketId, string $status): bool
    {
        return $this->update($ticketId, [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Add admin reply to ticket
     */
    public function addReply(int $ticketId, string $reply): bool
    {
        return $this->update($ticketId, [
            'admin_reply' => $reply,
            'status' => self::STATUS_PENDING,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Create a new support ticket
     */
    public function createTicket(int $userId, array $data): int
    {
        $ticketData = [
            'user_id' => $userId,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => self::STATUS_OPEN,
            'priority' => $data['priority'] ?? self::PRIORITY_MEDIUM,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($ticketData);
    }

    /**
     * Get tickets by status
     */
    public function findByStatus(string $status): array
    {
        return $this->findAll(['status' => $status], 'created_at DESC');
    }

    /**
     * Get all open tickets (for admin)
     */
    public function getOpenTickets(): array
    {
        return $this->findByStatus(self::STATUS_OPEN);
    }

    /**
     * Get all pending tickets (for admin)
     */
    public function getPendingTickets(): array
    {
        return $this->findByStatus(self::STATUS_PENDING);
    }

    /**
     * Close a ticket
     */
    public function close(int $ticketId): bool
    {
        return $this->updateStatus($ticketId, self::STATUS_CLOSED);
    }

    /**
     * Reopen a ticket
     */
    public function reopen(int $ticketId): bool
    {
        return $this->updateStatus($ticketId, self::STATUS_OPEN);
    }

    /**
     * Get ticket counts by status for a user
     */
    public function getStatusCounts(int $userId): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} WHERE user_id = :user_id GROUP BY status";
        $results = $this->query($sql, ['user_id' => $userId]);
        
        $counts = [
            self::STATUS_OPEN => 0,
            self::STATUS_PENDING => 0,
            self::STATUS_CLOSED => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }
        
        return $counts;
    }

    /**
     * Get all ticket counts by status (for admin)
     */
    public function getAllStatusCounts(): array
    {
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} GROUP BY status";
        $results = $this->query($sql, []);
        
        $counts = [
            self::STATUS_OPEN => 0,
            self::STATUS_PENDING => 0,
            self::STATUS_CLOSED => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
        }
        
        return $counts;
    }
}
