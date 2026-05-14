<?php
/**
 * ModelPricing Model
 * Handles pricing information for AI models
 */

class ModelPricing extends BaseModel
{
    protected string $table = 'model_pricing';
    
    protected array $fillable = [
        'provider_id',
        'model_name',
        'input_price_per_1k',
        'output_price_per_1k',
        'is_active',
        'created_at',
        'updated_at'
    ];

    /**
     * Find pricing for a specific model by name
     */
    public function findByModel(string $modelName): ?array
    {
        return $this->findBy(['model_name' => $modelName, 'is_active' => 1]);
    }

    /**
     * Find all models for a specific provider
     */
    public function findByProvider(int $providerId): array
    {
        return $this->findAll(['provider_id' => $providerId, 'is_active' => 1], 'model_name ASC');
    }

    /**
     * Calculate the cost for token usage
     * 
     * @param string $modelName The model name
     * @param int $inputTokens Number of input tokens used
     * @param int $outputTokens Number of output tokens used
     * @return float The calculated cost
     */
    public function calculateCost(string $modelName, int $inputTokens, int $outputTokens): float
    {
        $pricing = $this->findByModel($modelName);
        
        if (!$pricing) {
            return 0.0;
        }
        
        // Prices are per 1,000 tokens
        $inputCost = ($inputTokens / 1000) * (float) $pricing['input_price_per_1k'];
        $outputCost = ($outputTokens / 1000) * (float) $pricing['output_price_per_1k'];
        
        return $inputCost + $outputCost;
    }

    /**
     * Get pricing with provider information
     */
    public function findByModelWithProvider(string $modelName): ?array
    {
        $sql = "SELECT mp.*, p.name as provider_name, p.base_url 
                FROM {$this->table} mp 
                INNER JOIN providers p ON p.id = mp.provider_id 
                WHERE mp.model_name = :model_name AND mp.is_active = 1";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['model_name' => $modelName]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Get all active models with provider information
     */
    public function findAllActive(): array
    {
        $sql = "SELECT mp.*, p.name as provider_name 
                FROM {$this->table} mp 
                INNER JOIN providers p ON p.id = mp.provider_id 
                WHERE mp.is_active = 1 AND p.is_active = 1 
                ORDER BY p.name ASC, mp.model_name ASC";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Update pricing for a model
     */
    public function updatePricing(int $id, float $inputPrice, float $outputPrice): bool
    {
        return $this->update($id, [
            'input_price_per_1k' => $inputPrice,
            'output_price_per_1k' => $outputPrice,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    /**
     * Deactivate a model
     */
    public function deactivate(int $id): bool
    {
        return $this->update($id, ['is_active' => 0, 'updated_at' => date('Y-m-d H:i:s')]);
    }

    /**
     * Activate a model
     */
    public function activate(int $id): bool
    {
        return $this->update($id, ['is_active' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
