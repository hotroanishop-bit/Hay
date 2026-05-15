<?php
/**
 * Support Ticket Model
 * Enhanced ticket system with categories, ticket numbers, and assignments
 */

class SupportTicket extends BaseModel
{
    protected string $table = 'support_tickets';
    
    protected array $fillable = [
        'user_id',
        'ticket_number',
        'subject',
        'category',
        'priority',
        'status',
        'assigned_to',
        'created_at',
        'updated_at',
        'closed_at'
    ];

    // Ticket statuses
    const STATUS_OPEN = 'open';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_WAITING_REPLY = 'waiting_reply';
    const STATUS_RESOLVED = 'resolved';
    const STATUS_CLOSED = 'closed';

    // Ticket priorities
    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_URGENT = 'urgent';

    // Ticket categories
    const CATEGORY_BILLING = 'billing';
    const CATEGORY_TECHNICAL = 'technical';
    const CATEGORY_ACCOUNT = 'account';
    const CATEGORY_API = 'api';
    const CATEGORY_OTHER = 'other';

    /**
     * Get all statuses
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_OPEN => 'Open',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_WAITING_REPLY => 'Waiting Reply',
            self::STATUS_RESOLVED => 'Resolved',
            self::STATUS_CLOSED => 'Closed'
        ];
    }

    /**
     * Get all priorities
     */
    public static function getPriorities(): array
    {
        return [
            self::PRIORITY_LOW => 'Low',
            self::PRIORITY_MEDIUM => 'Medium',
            self::PRIORITY_HIGH => 'High',
            self::PRIORITY_URGENT => 'Urgent'
        ];
    }

    /**
     * Get all categories
     */
    public static function getCategories(): array
    {
        return [
            self::CATEGORY_BILLING => 'Billing',
            self::CATEGORY_TECHNICAL => 'Technical',
            self::CATEGORY_ACCOUNT => 'Account',
            self::CATEGORY_API => 'API',
            self::CATEGORY_OTHER => 'Other'
        ];
    }

    /**
     * Generate unique ticket number
     */
    public function generateTicketNumber(): string
    {
        $date = date('Ymd');
        $prefix = "TK-{$date}-";
        
        // Get the last ticket number for today
        $sql = "SELECT ticket_number FROM {$this->table} 
                WHERE ticket_number LIKE :prefix 
                ORDER BY id DESC LIMIT 1";
        $result = $this->query($sql, ['prefix' => $prefix . '%']);
        
        if (!empty($result)) {
            $lastNumber = (int) substr($result[0]['ticket_number'], -3);
            $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '001';
        }
        
        return $prefix . $newNumber;
    }

    /**
     * Create a new support ticket
     */
    public function createTicket(int $userId, array $data): int
    {
        $ticketData = [
            'user_id' => $userId,
            'ticket_number' => $this->generateTicketNumber(),
            'subject' => $data['subject'],
            'category' => $data['category'] ?? self::CATEGORY_OTHER,
            'priority' => $data['priority'] ?? self::PRIORITY_MEDIUM,
            'status' => self::STATUS_OPEN,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->create($ticketData);
    }

    /**
     * Find all tickets for a user with pagination
     */
    public function findByUser(int $userId, ?string $status = null, int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $params = ['user_id' => $userId];
        
        $whereClause = "WHERE user_id = :user_id";
        if ($status) {
            $whereClause .= " AND status = :status";
            $params['status'] = $status;
        }
        
        $sql = "SELECT t.*, 
                (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id = t.id) as message_count,
                (SELECT MAX(created_at) FROM ticket_messages WHERE ticket_id = t.id) as last_reply_at
                FROM {$this->table} t 
                {$whereClause}
                ORDER BY 
                    CASE WHEN t.status IN ('open', 'in_progress') THEN 0 ELSE 1 END,
                    t.updated_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->query($sql, $params);
    }

    /**
     * Find ticket with user info
     */
    public function findWithUser(int $ticketId): ?array
    {
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email,
                a.name as assigned_name
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN users a ON t.assigned_to = a.id
                WHERE t.id = :id";
        $result = $this->query($sql, ['id' => $ticketId]);
        return $result[0] ?? null;
    }

    /**
     * Find ticket by ticket number
     */
    public function findByNumber(string $ticketNumber): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE ticket_number = :ticket_number";
        $result = $this->query($sql, ['ticket_number' => $ticketNumber]);
        return $result[0] ?? null;
    }

    /**
     * Get all tickets for admin with filters
     */
    public function getAllTickets(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        $params = [];
        $conditions = [];
        
        if (!empty($filters['status'])) {
            $conditions[] = "t.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $conditions[] = "t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }
        
        if (!empty($filters['category'])) {
            $conditions[] = "t.category = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['assigned_to'])) {
            $conditions[] = "t.assigned_to = :assigned_to";
            $params['assigned_to'] = $filters['assigned_to'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(t.ticket_number LIKE :search OR t.subject LIKE :search OR u.name LIKE :search OR u.email LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT t.*, u.name as user_name, u.email as user_email,
                a.name as assigned_name,
                (SELECT COUNT(*) FROM ticket_messages WHERE ticket_id = t.id) as message_count,
                (SELECT MAX(created_at) FROM ticket_messages WHERE ticket_id = t.id) as last_reply_at
                FROM {$this->table} t
                LEFT JOIN users u ON t.user_id = u.id
                LEFT JOIN users a ON t.assigned_to = a.id
                {$whereClause}
                ORDER BY 
                    CASE WHEN t.priority = 'urgent' THEN 0
                         WHEN t.priority = 'high' THEN 1
                         WHEN t.priority = 'medium' THEN 2
                         ELSE 3 END,
                    CASE WHEN t.status IN ('open', 'in_progress') THEN 0 ELSE 1 END,
                    t.updated_at DESC
                LIMIT {$perPage} OFFSET {$offset}";
        
        return $this->query($sql, $params);
    }

    /**
     * Count all tickets with filters
     */
    public function countAllTickets(array $filters = []): int
    {
        $params = [];
        $conditions = [];
        
        if (!empty($filters['status'])) {
            $conditions[] = "t.status = :status";
            $params['status'] = $filters['status'];
        }
        
        if (!empty($filters['priority'])) {
            $conditions[] = "t.priority = :priority";
            $params['priority'] = $filters['priority'];
        }
        
        if (!empty($filters['category'])) {
            $conditions[] = "t.category = :category";
            $params['category'] = $filters['category'];
        }
        
        if (!empty($filters['search'])) {
            $conditions[] = "(t.ticket_number LIKE :search OR t.subject LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }
        
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} t {$whereClause}";
        $result = $this->query($sql, $params);
        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Update ticket status
     */
    public function updateStatus(int $ticketId, string $status): bool
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($status === self::STATUS_CLOSED || $status === self::STATUS_RESOLVED) {
            $data['closed_at'] = date('Y-m-d H:i:s');
        }
        
        return $this->update($ticketId, $data);
    }

    /**
     * Assign ticket to admin
     */
    public function assign(int $ticketId, ?int $adminId): bool
    {
        return $this->update($ticketId, [
            'assigned_to' => $adminId,
            'status' => $adminId ? self::STATUS_IN_PROGRESS : self::STATUS_OPEN,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Get ticket counts by status for a user
     */
    public function getStatusCounts(?int $userId = null): array
    {
        $params = [];
        $whereClause = '';
        
        if ($userId) {
            $whereClause = 'WHERE user_id = :user_id';
            $params['user_id'] = $userId;
        }
        
        $sql = "SELECT status, COUNT(*) as count FROM {$this->table} {$whereClause} GROUP BY status";
        $results = $this->query($sql, $params);
        
        $counts = [
            self::STATUS_OPEN => 0,
            self::STATUS_IN_PROGRESS => 0,
            self::STATUS_WAITING_REPLY => 0,
            self::STATUS_RESOLVED => 0,
            self::STATUS_CLOSED => 0,
            'total' => 0
        ];
        
        foreach ($results as $row) {
            $counts[$row['status']] = (int) $row['count'];
            $counts['total'] += (int) $row['count'];
        }
        
        return $counts;
    }

    /**
     * Get admin stats
     */
    public function getAdminStats(): array
    {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) as open,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) as in_progress,
                SUM(CASE WHEN status = 'waiting_reply' THEN 1 ELSE 0 END) as waiting_reply,
                SUM(CASE WHEN priority = 'urgent' AND status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) as urgent
                FROM {$this->table}";
        $result = $this->query($sql, []);
        return $result[0] ?? [];
    }

    /**
     * Close ticket
     */
    public function close(int $ticketId): bool
    {
        return $this->updateStatus($ticketId, self::STATUS_CLOSED);
    }

    /**
     * Reopen ticket
     */
    public function reopen(int $ticketId): bool
    {
        return $this->update($ticketId, [
            'status' => self::STATUS_OPEN,
            'closed_at' => null,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }
}
