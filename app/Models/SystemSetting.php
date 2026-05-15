<?php
/**
 * SystemSetting Model
 * Enhanced settings model with groups, labels, and sensitive flag
 * Supports typed values and admin tracking
 */

class SystemSetting extends BaseModel
{
    protected string $table = 'system_settings';
    
    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'setting_group',
        'label',
        'description',
        'is_sensitive',
        'updated_at',
        'updated_by'
    ];

    /**
     * Get a single setting value by key
     */
    public function get(string $key, $default = null)
    {
        $result = $this->findBy(['setting_key' => $key]);
        
        if (!$result) {
            return $default;
        }
        
        return $this->castValue($result);
    }

    /**
     * Get a single setting row with all metadata
     */
    public function getWithMeta(string $key): ?array
    {
        return $this->findBy(['setting_key' => $key]);
    }

    /**
     * Get all settings in a specific group
     */
    public function getGroup(string $group): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE setting_group = :group ORDER BY id ASC";
        return $this->query($sql, ['group' => $group]);
    }

    /**
     * Get all settings as key-value array
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY setting_group, id";
        $settings = $this->query($sql);
        
        $result = [];
        foreach ($settings as $s) {
            $result[$s['setting_key']] = $this->castValue($s);
        }
        
        return $result;
    }

    /**
     * Get all settings with full metadata grouped by setting_group
     */
    public function getAllGrouped(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY setting_group, id";
        $settings = $this->query($sql);
        
        $grouped = [];
        foreach ($settings as $s) {
            $group = $s['setting_group'] ?? 'general';
            if (!isset($grouped[$group])) {
                $grouped[$group] = [];
            }
            $grouped[$group][] = $s;
        }
        
        return $grouped;
    }

    /**
     * Get available setting groups
     */
    public function getGroups(): array
    {
        $sql = "SELECT DISTINCT setting_group FROM {$this->table} ORDER BY setting_group";
        $results = $this->query($sql);
        
        return array_column($results, 'setting_group');
    }

    /**
     * Update a setting value
     */
    public function set(string $key, $value, ?int $updatedBy = null): bool
    {
        // Serialize value based on current type
        $existing = $this->findBy(['setting_key' => $key]);
        
        if (!$existing) {
            return false;
        }
        
        $serialized = $this->serializeValue($value, $existing['setting_type']);
        
        $sql = "UPDATE {$this->table} 
                SET setting_value = :value, 
                    updated_by = :updated_by, 
                    updated_at = NOW() 
                WHERE setting_key = :key";
        
        return $this->execute($sql, [
            'value' => $serialized,
            'updated_by' => $updatedBy,
            'key' => $key
        ]);
    }

    /**
     * Batch update multiple settings
     */
    public function setMany(array $settings, ?int $updatedBy = null): int
    {
        $count = 0;
        foreach ($settings as $key => $value) {
            if ($this->set($key, $value, $updatedBy)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * Create a new setting
     */
    public function createSetting(array $data): int
    {
        $defaults = [
            'setting_type' => 'string',
            'setting_group' => 'general',
            'is_sensitive' => 0,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        $data = array_merge($defaults, $data);
        
        // Serialize the value
        if (isset($data['setting_value'])) {
            $data['setting_value'] = $this->serializeValue(
                $data['setting_value'], 
                $data['setting_type']
            );
        }
        
        return $this->create($data);
    }

    /**
     * Delete a setting
     */
    public function deleteSetting(string $key): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE setting_key = :key";
        return $this->execute($sql, ['key' => $key]);
    }

    /**
     * Check if a setting exists
     */
    public function exists(string $key): bool
    {
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table} WHERE setting_key = :key";
        $result = $this->query($sql, ['key' => $key]);
        return (int) ($result[0]['cnt'] ?? 0) > 0;
    }

    /**
     * Cast value based on setting type
     */
    private function castValue(array $setting)
    {
        $value = $setting['setting_value'] ?? null;
        $type = $setting['setting_type'] ?? 'string';
        
        if ($value === null) {
            return null;
        }
        
        switch ($type) {
            case 'number':
                return is_numeric($value) ? (strpos($value, '.') !== false ? (float) $value : (int) $value) : 0;
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'json':
                return json_decode($value, true) ?? [];
            case 'text':
            case 'string':
            default:
                return $value;
        }
    }

    /**
     * Serialize value for storage
     */
    private function serializeValue($value, string $type): string
    {
        switch ($type) {
            case 'boolean':
                return $value ? '1' : '0';
            case 'json':
                return is_string($value) ? $value : json_encode($value);
            case 'number':
                return (string) $value;
            default:
                return (string) $value;
        }
    }
}
