<?php
/**
 * Setting Model
 * Handles application settings stored in database
 */

class Setting extends BaseModel
{
    protected string $table = 'settings';
    
    protected array $fillable = [
        'setting_key',
        'setting_value',
        'setting_type',
        'created_at',
        'updated_at'
    ];

    /**
     * Get a setting value by key
     */
    public function get(string $key, $default = null): mixed
    {
        $sql = "SELECT setting_value, setting_type FROM {$this->table} WHERE setting_key = :key LIMIT 1";
        $results = $this->query($sql, ['key' => $key]);
        
        if (empty($results)) {
            return $default;
        }
        
        return $this->castValue($results[0]['setting_value'], $results[0]['setting_type']);
    }

    /**
     * Set a setting value (create or update)
     */
    public function set(string $key, $value, string $type = 'string'): bool
    {
        $stringValue = $this->serializeValue($value, $type);
        
        // Check if setting exists
        $existing = $this->findBy(['setting_key' => $key]);
        
        if ($existing) {
            $sql = "UPDATE {$this->table} SET setting_value = :value, setting_type = :type, updated_at = NOW() WHERE setting_key = :key";
            return $this->execute($sql, [
                'key' => $key,
                'value' => $stringValue,
                'type' => $type
            ]);
        } else {
            return $this->create([
                'setting_key' => $key,
                'setting_value' => $stringValue,
                'setting_type' => $type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]) > 0;
        }
    }

    /**
     * Get all settings as key => value array
     */
    public function getAll(): array
    {
        $sql = "SELECT setting_key, setting_value, setting_type FROM {$this->table}";
        $results = $this->query($sql);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $this->castValue($row['setting_value'], $row['setting_type']);
        }
        
        return $settings;
    }

    /**
     * Get settings filtered by type
     */
    public function getByType(string $type): array
    {
        $sql = "SELECT setting_key, setting_value, setting_type FROM {$this->table} WHERE setting_type = :type";
        $results = $this->query($sql, ['type' => $type]);
        
        $settings = [];
        foreach ($results as $row) {
            $settings[$row['setting_key']] = $this->castValue($row['setting_value'], $row['setting_type']);
        }
        
        return $settings;
    }

    /**
     * Remove a setting
     */
    public function remove(string $key): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE setting_key = :key";
        return $this->execute($sql, ['key' => $key]);
    }

    /**
     * Cast value based on type
     */
    private function castValue(?string $value, string $type): mixed
    {
        if ($value === null) {
            return null;
        }
        
        return match ($type) {
            'int' => (int) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($value, true),
            default => $value
        };
    }

    /**
     * Serialize value for storage
     */
    private function serializeValue($value, string $type): string
    {
        return match ($type) {
            'bool' => $value ? '1' : '0',
            'json' => json_encode($value),
            default => (string) $value
        };
    }
}
