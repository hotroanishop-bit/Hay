<?php
/**
 * API Service
 * Handles API key creation, management, and validation
 */

class APIService
{
    private ApiKey $apiKeyModel;
    private UsageLog $usageLogModel;

    public function __construct(ApiKey $apiKeyModel, UsageLog $usageLogModel)
    {
        $this->apiKeyModel = $apiKeyModel;
        $this->usageLogModel = $usageLogModel;
    }

    /**
     * Create a new API key for a user
     */
    public function createKey(int $userId, array $data): array
    {
        $keyData = [
            'name' => $data['name'] ?? 'Unnamed Key',
            'provider' => $data['provider'] ?? null,
            'model' => $data['model'] ?? null,
            'rate_limit' => $data['rate_limit'] ?? null,
            'usage_limit' => $data['usage_limit'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
        ];

        $result = $this->apiKeyModel->createKey($userId, $keyData);

        return [
            'id' => $result['id'],
            'key' => $result['plain_key'],
            'name' => $keyData['name'],
            'created_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Rotate an API key (generate new key, keep same settings)
     */
    public function rotateKey(int $keyId): ?array
    {
        $existingKey = $this->apiKeyModel->find($keyId);

        if (!$existingKey) {
            return null;
        }

        // Deactivate old key
        $this->apiKeyModel->deactivate($keyId);

        // Create new key with same settings
        $newKeyData = [
            'name' => $existingKey['name'] . ' (rotated)',
            'provider' => $existingKey['provider'],
            'model' => $existingKey['model'],
            'rate_limit' => $existingKey['rate_limit'],
            'usage_limit' => $existingKey['usage_limit'],
            'expires_at' => $existingKey['expires_at'],
        ];

        $result = $this->apiKeyModel->createKey($existingKey['user_id'], $newKeyData);

        return [
            'old_key_id' => $keyId,
            'new_key_id' => $result['id'],
            'new_key' => $result['plain_key'],
        ];
    }

    /**
     * Revoke (deactivate) an API key
     */
    public function revokeKey(int $keyId): bool
    {
        $key = $this->apiKeyModel->find($keyId);

        if (!$key) {
            return false;
        }

        return $this->apiKeyModel->deactivate($keyId);
    }

    /**
     * Validate an API key and return key details if valid
     */
    public function validateKey(string $key): ?array
    {
        $keyRecord = $this->apiKeyModel->findByPlainKey($key);

        if (!$keyRecord) {
            return null;
        }

        if (!$this->apiKeyModel->isValid($keyRecord['id'])) {
            return null;
        }

        return [
            'id' => $keyRecord['id'],
            'user_id' => $keyRecord['user_id'],
            'name' => $keyRecord['name'],
            'provider' => $keyRecord['provider'],
            'model' => $keyRecord['model'],
            'rate_limit' => $keyRecord['rate_limit'],
            'usage_count' => $keyRecord['usage_count'],
            'usage_limit' => $keyRecord['usage_limit'],
        ];
    }

    /**
     * Get statistics for an API key
     */
    public function getKeyStats(int $keyId): array
    {
        $key = $this->apiKeyModel->find($keyId);

        if (!$key) {
            return [];
        }

        $stats = $this->usageLogModel->getStatsByKey($keyId);

        return [
            'key_id' => $keyId,
            'name' => $key['name'],
            'is_active' => (bool) $key['is_active'],
            'usage_count' => $key['usage_count'],
            'usage_limit' => $key['usage_limit'],
            'rate_limit' => $key['rate_limit'],
            'total_requests' => (int) ($stats['total_requests'] ?? 0),
            'total_tokens' => (int) ($stats['total_tokens'] ?? 0),
            'total_cost' => (float) ($stats['total_cost'] ?? 0),
            'avg_tokens_per_request' => (float) ($stats['avg_tokens_per_request'] ?? 0),
            'created_at' => $key['created_at'],
            'expires_at' => $key['expires_at'],
        ];
    }

    /**
     * List available API providers
     */
    public function listProviders(): array
    {
        return [
            [
                'id' => 'openai',
                'name' => 'OpenAI',
                'description' => 'GPT models for text generation and chat',
            ],
            [
                'id' => 'anthropic',
                'name' => 'Anthropic',
                'description' => 'Claude models for advanced reasoning',
            ],
            [
                'id' => 'google',
                'name' => 'Google AI',
                'description' => 'Gemini and PaLM models',
            ],
            [
                'id' => 'mistral',
                'name' => 'Mistral AI',
                'description' => 'Open-weight language models',
            ],
            [
                'id' => 'cohere',
                'name' => 'Cohere',
                'description' => 'Enterprise-focused language models',
            ],
        ];
    }

    /**
     * List available models for a provider
     */
    public function listModels(string $provider): array
    {
        $models = [
            'openai' => [
                ['id' => 'gpt-4', 'name' => 'GPT-4', 'price_per_1k' => 0.03],
                ['id' => 'gpt-4-turbo', 'name' => 'GPT-4 Turbo', 'price_per_1k' => 0.01],
                ['id' => 'gpt-3.5-turbo', 'name' => 'GPT-3.5 Turbo', 'price_per_1k' => 0.0015],
            ],
            'anthropic' => [
                ['id' => 'claude-3-opus', 'name' => 'Claude 3 Opus', 'price_per_1k' => 0.015],
                ['id' => 'claude-3-sonnet', 'name' => 'Claude 3 Sonnet', 'price_per_1k' => 0.003],
                ['id' => 'claude-3-haiku', 'name' => 'Claude 3 Haiku', 'price_per_1k' => 0.00025],
            ],
            'google' => [
                ['id' => 'gemini-pro', 'name' => 'Gemini Pro', 'price_per_1k' => 0.0005],
                ['id' => 'gemini-ultra', 'name' => 'Gemini Ultra', 'price_per_1k' => 0.02],
            ],
            'mistral' => [
                ['id' => 'mistral-large', 'name' => 'Mistral Large', 'price_per_1k' => 0.008],
                ['id' => 'mistral-medium', 'name' => 'Mistral Medium', 'price_per_1k' => 0.0027],
                ['id' => 'mistral-small', 'name' => 'Mistral Small', 'price_per_1k' => 0.002],
            ],
            'cohere' => [
                ['id' => 'command', 'name' => 'Command', 'price_per_1k' => 0.015],
                ['id' => 'command-light', 'name' => 'Command Light', 'price_per_1k' => 0.003],
            ],
        ];

        return $models[$provider] ?? [];
    }
}
