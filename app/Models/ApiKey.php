<?php
/**
 * ApiKey Model
 * Handles API key management and usage tracking
 */

class ApiKey extends BaseModel
{
    protected string $table = 'api_keys';
    
    protected array $fillable = [
        'user_id',
        'key_hash',
        'name',
        'provider',
        'model',
        'allowed_models',
        'allowed_ips',
        'rate_limit',
        'usage_count',
        'usage_limit',
        'is_active',
        'expires_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Find all API keys for a user
     */
    public function findByUser(int $userId): array
    {
        return $this->findAll(['user_id' => $userId], 'created_at DESC');
    }

    /**
     * Generate a new API key
     * Returns the plain text key (only shown once) and the hash
     */
    public function generateKey(): array
    {
        $config = require CONFIG_PATH . '/app.php';
        $prefix = $config['api_key']['prefix'] ?? 'hay_';
        $length = $config['api_key']['length'] ?? 32;
        
        $randomBytes = bin2hex(random_bytes($length / 2));
        $plainKey = $prefix . $randomBytes;
        $keyHash = hash('sha256', $plainKey);
        
        return [
            'plain' => $plainKey,
            'hash' => $keyHash
        ];
    }

    /**
     * Create a new API key for a user
     */
    public function createKey(int $userId, array $data): array
    {
        $keyPair = $this->generateKey();
        $config = require CONFIG_PATH . '/app.php';
        
        // Process allowed_models - convert to JSON if array
        $allowedModels = null;
        if (isset($data['allowed_models']) && !empty($data['allowed_models'])) {
            $allowedModels = is_array($data['allowed_models']) 
                ? json_encode(array_values($data['allowed_models']))
                : $data['allowed_models'];
        }
        
        // Process allowed_ips - convert to JSON if array
        $allowedIps = null;
        if (isset($data['allowed_ips']) && !empty($data['allowed_ips'])) {
            $allowedIps = is_array($data['allowed_ips'])
                ? json_encode(array_values($data['allowed_ips']))
                : $data['allowed_ips'];
        }
        
        $keyData = array_merge($data, [
            'user_id' => $userId,
            'key_hash' => $keyPair['hash'],
            'rate_limit' => $data['rate_limit'] ?? $config['api_key']['default_rate_limit'],
            'usage_limit' => $data['usage_limit'] ?? $config['api_key']['default_usage_limit'],
            'allowed_models' => $allowedModels,
            'allowed_ips' => $allowedIps,
            'usage_count' => 0,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        $id = $this->create($keyData);
        
        return [
            'id' => $id,
            'plain_key' => $keyPair['plain']
        ];
    }

    /**
     * Find API key by its hash
     */
    public function findByHash(string $hash): ?array
    {
        return $this->findBy(['key_hash' => $hash]);
    }

    /**
     * Find API key by plain text key
     */
    public function findByPlainKey(string $plainKey): ?array
    {
        $hash = hash('sha256', $plainKey);
        return $this->findByHash($hash);
    }

    /**
     * Increment usage count for an API key
     */
    public function incrementUsage(int $keyId, int $amount = 1): bool
    {
        $sql = "UPDATE {$this->table} SET usage_count = usage_count + :amount, updated_at = NOW() WHERE id = :id";
        return $this->execute($sql, ['amount' => $amount, 'id' => $keyId]);
    }

    /**
     * Check if API key has exceeded its usage limit
     */
    public function checkLimit(int $keyId): bool
    {
        $key = $this->find($keyId);
        if (!$key) {
            return false;
        }
        
        // If usage_limit is 0 or null, no limit
        if (empty($key['usage_limit'])) {
            return true;
        }
        
        return $key['usage_count'] < $key['usage_limit'];
    }

    /**
     * Check if API key is valid (active, not expired, within limits)
     */
    public function isValid(int $keyId): bool
    {
        $key = $this->find($keyId);
        if (!$key) {
            return false;
        }
        
        // Check if active
        if (!$key['is_active']) {
            return false;
        }
        
        // Check if expired
        if ($key['expires_at'] && strtotime($key['expires_at']) < time()) {
            return false;
        }
        
        // Check usage limit
        return $this->checkLimit($keyId);
    }

    /**
     * Deactivate an API key
     */
    public function deactivate(int $keyId): bool
    {
        return $this->update($keyId, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Activate an API key
     */
    public function activate(int $keyId): bool
    {
        return $this->update($keyId, ['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Get active keys count for a user
     */
    public function getActiveCount(int $userId): int
    {
        return $this->count(['user_id' => $userId, 'is_active' => 1]);
    }

    /**
     * Check if a model is allowed for a given API key
     * 
     * @param array $keyData The API key data array
     * @param string $model The model name to check
     * @return bool True if the model is allowed
     */
    public function isModelAllowed(array $keyData, string $model): bool
    {
        // Get allowed_models from key data
        $allowedModels = $keyData['allowed_models'] ?? null;
        
        // Null or empty means all models are allowed
        if ($allowedModels === null || $allowedModels === '') {
            return true;
        }
        
        // Parse JSON if string
        if (is_string($allowedModels)) {
            $allowedModels = json_decode($allowedModels, true);
        }
        
        // Empty array means all allowed
        if (empty($allowedModels) || !is_array($allowedModels)) {
            return true;
        }
        
        // Check if model is in allowed list
        return in_array($model, $allowedModels, true);
    }

    /**
     * Check if an IP address is allowed for a given API key
     * 
     * @param array $keyData The API key data array
     * @param string $ip The IP address to check
     * @return bool True if the IP is allowed
     */
    public function isIPAllowed(array $keyData, string $ip): bool
    {
        // Get allowed_ips from key data
        $allowedIps = $keyData['allowed_ips'] ?? null;
        
        // Null or empty means all IPs are allowed
        if ($allowedIps === null || $allowedIps === '') {
            return true;
        }
        
        // Parse JSON if string
        if (is_string($allowedIps)) {
            $allowedIps = json_decode($allowedIps, true);
        }
        
        // Empty array means all allowed
        if (empty($allowedIps) || !is_array($allowedIps)) {
            return true;
        }
        
        // Use IPWhitelistService for matching
        $ipService = new IPWhitelistService();
        return $ipService->isIPAllowed($allowedIps, $ip);
    }

    /**
     * Get allowed models as array from key data
     * 
     * @param array $keyData The API key data array
     * @return array|null Array of model names or null if all allowed
     */
    public function getAllowedModels(array $keyData): ?array
    {
        $allowedModels = $keyData['allowed_models'] ?? null;
        
        if ($allowedModels === null || $allowedModels === '') {
            return null;
        }
        
        if (is_string($allowedModels)) {
            $allowedModels = json_decode($allowedModels, true);
        }
        
        return is_array($allowedModels) && !empty($allowedModels) ? $allowedModels : null;
    }

    /**
     * Get allowed IPs as array from key data
     * 
     * @param array $keyData The API key data array
     * @return array|null Array of IP addresses/patterns or null if all allowed
     */
    public function getAllowedIPs(array $keyData): ?array
    {
        $allowedIps = $keyData['allowed_ips'] ?? null;
        
        if ($allowedIps === null || $allowedIps === '') {
            return null;
        }
        
        if (is_string($allowedIps)) {
            $allowedIps = json_decode($allowedIps, true);
        }
        
        return is_array($allowedIps) && !empty($allowedIps) ? $allowedIps : null;
    }
}
