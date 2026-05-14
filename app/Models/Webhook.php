<?php
/**
 * Webhook Model
 * Handles user webhook configurations
 */

class Webhook extends BaseModel
{
    protected string $table = 'webhooks';
    
    protected array $fillable = [
        'user_id',
        'url',
        'secret',
        'events',
        'is_active',
        'created_at',
        'updated_at'
    ];

    /**
     * Available webhook events
     */
    public const EVENTS = [
        'deposit_approved' => 'Deposit Approved',
        'deposit_rejected' => 'Deposit Rejected',
        'low_balance' => 'Low Balance Warning',
        'key_quota_warning' => 'API Key Quota Warning',
        'plan_expired' => 'Plan Expired'
    ];

    /**
     * Get all webhooks for a user
     */
    public function getByUser(int $userId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $webhooks = $stmt->fetchAll();
        
        // Decode events JSON
        foreach ($webhooks as &$webhook) {
            if (is_string($webhook['events'])) {
                $webhook['events'] = json_decode($webhook['events'], true) ?? [];
            }
        }
        
        return $webhooks;
    }

    /**
     * Get active webhooks for a specific event
     */
    public function getActiveByEvent(string $event): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        
        $allWebhooks = $stmt->fetchAll();
        $matchingWebhooks = [];
        
        foreach ($allWebhooks as $webhook) {
            $events = is_string($webhook['events']) 
                ? json_decode($webhook['events'], true) ?? [] 
                : $webhook['events'];
            
            if (in_array($event, $events)) {
                $webhook['events'] = $events;
                $matchingWebhooks[] = $webhook;
            }
        }
        
        return $matchingWebhooks;
    }

    /**
     * Get active webhooks for a specific user and event
     */
    public function getActiveByUserAndEvent(int $userId, string $event): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND is_active = 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        
        $userWebhooks = $stmt->fetchAll();
        $matchingWebhooks = [];
        
        foreach ($userWebhooks as $webhook) {
            $events = is_string($webhook['events']) 
                ? json_decode($webhook['events'], true) ?? [] 
                : $webhook['events'];
            
            if (in_array($event, $events)) {
                $webhook['events'] = $events;
                $matchingWebhooks[] = $webhook;
            }
        }
        
        return $matchingWebhooks;
    }

    /**
     * Create a new webhook
     */
    public function createWebhook(array $data): int
    {
        // Ensure events is JSON encoded
        if (isset($data['events']) && is_array($data['events'])) {
            $data['events'] = json_encode($data['events']);
        }
        
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Update a webhook
     */
    public function updateWebhook(int $id, array $data): bool
    {
        // Ensure events is JSON encoded
        if (isset($data['events']) && is_array($data['events'])) {
            $data['events'] = json_encode($data['events']);
        }
        
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Delete a webhook
     */
    public function deleteWebhook(int $id): bool
    {
        return $this->delete($id);
    }

    /**
     * Find webhook by ID with decoded events
     */
    public function findWithEvents(int $id): ?array
    {
        $webhook = $this->find($id);
        
        if ($webhook && is_string($webhook['events'])) {
            $webhook['events'] = json_decode($webhook['events'], true) ?? [];
        }
        
        return $webhook;
    }

    /**
     * Toggle webhook active status
     */
    public function toggleActive(int $id): bool
    {
        $sql = "UPDATE {$this->table} SET is_active = NOT is_active, updated_at = :updated_at WHERE id = :id";
        return $this->execute($sql, ['id' => $id, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get webhook count for a user
     */
    public function countByUser(int $userId): int
    {
        return $this->count(['user_id' => $userId]);
    }

    /**
     * Generate a random secret key
     */
    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}
