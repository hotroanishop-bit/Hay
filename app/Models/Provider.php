<?php
/**
 * Provider Model
 * Handles AI API providers (OpenAI, Anthropic, etc.)
 */

class Provider extends BaseModel
{
    protected string $table = 'providers';
    
    protected array $fillable = [
        'name',
        'base_url',
        'api_key_encrypted',
        'is_active',
        'created_at',
        'updated_at'
    ];

    /**
     * Find all active providers
     */
    public function findActive(): array
    {
        return $this->findAll(['is_active' => 1], 'name ASC');
    }

    /**
     * Find a provider by name
     */
    public function findByName(string $name): ?array
    {
        return $this->findBy(['name' => $name]);
    }

    /**
     * Get all models for a provider
     */
    public function getProviderModels(int $providerId): array
    {
        $sql = "SELECT * FROM model_pricing WHERE provider_id = :provider_id AND is_active = 1 ORDER BY model_name ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['provider_id' => $providerId]);
        
        return $stmt->fetchAll();
    }

    /**
     * Decrypt the stored API key for a provider
     * Currently uses base64 encoding (can be upgraded to proper encryption later)
     */
    public function decryptApiKey(int $providerId): ?string
    {
        $provider = $this->find($providerId);
        
        if (!$provider || empty($provider['api_key_encrypted'])) {
            return null;
        }
        
        // Simple base64 decoding for now
        // TODO: Upgrade to proper encryption (AES-256-GCM) with app key
        $decoded = base64_decode($provider['api_key_encrypted'], true);
        
        return $decoded !== false ? $decoded : null;
    }

    /**
     * Encrypt and store an API key for a provider
     * Currently uses base64 encoding (can be upgraded to proper encryption later)
     */
    public function encryptApiKey(int $providerId, string $apiKey): bool
    {
        // Simple base64 encoding for now
        // TODO: Upgrade to proper encryption (AES-256-GCM) with app key
        $encrypted = base64_encode($apiKey);
        
        return $this->update($providerId, [
            'api_key_encrypted' => $encrypted,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Deactivate a provider
     */
    public function deactivate(int $providerId): bool
    {
        return $this->update($providerId, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Activate a provider
     */
    public function activate(int $providerId): bool
    {
        return $this->update($providerId, ['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
