<?php
/**
 * Theme Model
 * Handles UI themes with CSS variables stored as JSON
 */

class Theme extends BaseModel
{
    protected string $table = 'themes';
    
    protected array $fillable = [
        'name',
        'css_variables',
        'is_default',
        'created_by',
        'created_at'
    ];

    /**
     * Find the default theme
     */
    public function findDefault(): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_default = 1 LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Find all active themes
     */
    public function findActive(): array
    {
        return $this->findAll([], 'name ASC');
    }

    /**
     * Find a theme by name
     */
    public function findByName(string $name): ?array
    {
        return $this->findBy(['name' => $name]);
    }

    /**
     * Set a theme as the default (unset others)
     */
    public function setDefault(int $id): bool
    {
        $this->beginTransaction();
        
        try {
            // Unset all defaults
            $sql = "UPDATE {$this->table} SET is_default = 0";
            $this->execute($sql);
            
            // Set new default
            $sql = "UPDATE {$this->table} SET is_default = 1 WHERE id = :id";
            $result = $this->execute($sql, ['id' => $id]);
            
            $this->commit();
            return $result;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Get CSS variables for a theme
     */
    public function getCssVariables(int $id): ?array
    {
        $theme = $this->find($id);
        
        if (!$theme) {
            return null;
        }
        
        $cssVariables = $theme['css_variables'];
        
        // Decode JSON if stored as string
        if (is_string($cssVariables)) {
            $decoded = json_decode($cssVariables, true);
            return $decoded ?: [];
        }
        
        return is_array($cssVariables) ? $cssVariables : [];
    }
}
