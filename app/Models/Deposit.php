<?php
/**
 * Deposit Model
 * Handles deposit requests and bank transfer payments
 */

class Deposit extends BaseModel
{
    protected string $table = 'deposits';
    
    protected array $fillable = [
        'user_id',
        'amount',
        'reference_code',
        'bank_account',
        'status',
        'qr_data',
        'created_at',
        'processed_at',
        'processed_by'
    ];

    /**
     * Find all deposits for a user
     */
    public function findByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        return $this->query($sql, ['user_id' => $userId]);
    }

    /**
     * Find deposit by reference code
     */
    public function findByReference(string $code): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE reference_code = :code LIMIT 1";
        $results = $this->query($sql, ['code' => $code]);
        return $results[0] ?? null;
    }

    /**
     * Find all pending deposits
     */
    public function findPending(): array
    {
        $sql = "SELECT d.*, u.name as user_name, u.email as user_email 
                FROM {$this->table} d 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE d.status = 'pending' 
                ORDER BY d.created_at ASC";
        return $this->query($sql);
    }

    /**
     * Find deposits by status
     */
    public function findByStatus(string $status): array
    {
        $sql = "SELECT d.*, u.name as user_name, u.email as user_email 
                FROM {$this->table} d 
                LEFT JOIN users u ON d.user_id = u.id 
                WHERE d.status = :status 
                ORDER BY d.created_at DESC";
        return $this->query($sql, ['status' => $status]);
    }

    /**
     * Update deposit status with processor information
     */
    public function updateStatus(int $id, string $status, ?int $processedBy = null): bool
    {
        $sql = "UPDATE {$this->table} SET status = :status, processed_at = NOW(), processed_by = :processed_by WHERE id = :id";
        return $this->execute($sql, [
            'id' => $id,
            'status' => $status,
            'processed_by' => $processedBy
        ]);
    }

    /**
     * Create a new deposit request
     */
    public function createDeposit(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'pending';
        return $this->create($data);
    }

    /**
     * Expire old pending deposits
     */
    public function expirePendingDeposits(int $hoursOld = 24): int
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'expired', processed_at = NOW() 
                WHERE status = 'pending' 
                AND created_at < DATE_SUB(NOW(), INTERVAL :hours HOUR)";
        $this->execute($sql, ['hours' => $hoursOld]);
        
        // Return the number of affected rows
        $stmt = $this->db()->prepare("SELECT ROW_COUNT() as affected");
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) ($result['affected'] ?? 0);
    }
}
