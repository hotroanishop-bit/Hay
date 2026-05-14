<?php
/**
 * Transaction Model
 * Handles billing and transaction history
 */

class Transaction extends BaseModel
{
    protected string $table = 'transactions';
    
    protected array $fillable = [
        'user_id',
        'type',
        'amount',
        'description',
        'reference_id',
        'payment_method',
        'status',
        'created_at'
    ];

    // Transaction types
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    // Transaction statuses
    const STATUS_PENDING = 'pending';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Find all transactions for a user
     */
    public function findByUser(int $userId, int $limit = 50): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC', $limit);
    }

    /**
     * Create a credit transaction (add funds)
     */
    public function createCredit(int $userId, float $amount, string $description, array $extra = []): int
    {
        $data = array_merge([
            'user_id' => $userId,
            'type' => self::TYPE_CREDIT,
            'amount' => $amount,
            'description' => $description,
            'status' => self::STATUS_COMPLETED,
            'created_at' => date('Y-m-d H:i:s')
        ], $extra);
        
        return $this->create($data);
    }

    /**
     * Create a debit transaction (use funds)
     */
    public function createDebit(int $userId, float $amount, string $description, array $extra = []): int
    {
        $data = array_merge([
            'user_id' => $userId,
            'type' => self::TYPE_DEBIT,
            'amount' => $amount,
            'description' => $description,
            'status' => self::STATUS_COMPLETED,
            'created_at' => date('Y-m-d H:i:s')
        ], $extra);
        
        return $this->create($data);
    }

    /**
     * Get transaction history with pagination
     */
    public function getHistory(int $userId, int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        $transactions = $this->findAll(['user_id' => $userId], 'created_at DESC', $perPage, $offset);
        $total = $this->count(['user_id' => $userId]);
        
        return [
            'data' => $transactions,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => ceil($total / $perPage)
        ];
    }

    /**
     * Get transactions by type
     */
    public function findByType(int $userId, string $type): array
    {
        return $this->findAll(['user_id' => $userId, 'type' => $type], 'created_at DESC');
    }

    /**
     * Get total credits for a user
     */
    public function getTotalCredits(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM {$this->table} WHERE user_id = :user_id AND type = :type AND status = :status";
        $result = $this->query($sql, [
            'user_id' => $userId,
            'type' => self::TYPE_CREDIT,
            'status' => self::STATUS_COMPLETED
        ]);
        
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Get total debits for a user
     */
    public function getTotalDebits(int $userId): float
    {
        $sql = "SELECT COALESCE(SUM(amount), 0) as total FROM {$this->table} WHERE user_id = :user_id AND type = :type AND status = :status";
        $result = $this->query($sql, [
            'user_id' => $userId,
            'type' => self::TYPE_DEBIT,
            'status' => self::STATUS_COMPLETED
        ]);
        
        return (float) ($result[0]['total'] ?? 0);
    }

    /**
     * Find transaction by reference ID
     */
    public function findByReference(string $referenceId): ?array
    {
        return $this->findBy(['reference_id' => $referenceId]);
    }

    /**
     * Update transaction status
     */
    public function updateStatus(int $transactionId, string $status): bool
    {
        return $this->update($transactionId, ['status' => $status]);
    }

    /**
     * Get recent transactions for a user
     */
    public function getRecent(int $userId, int $limit = 5): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC', $limit);
    }
}
