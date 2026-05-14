<?php
/**
 * Changelog Model
 * Handles changelog/version updates data operations
 */

class Changelog extends BaseModel
{
    protected string $table = 'changelogs';
    
    protected array $fillable = [
        'version',
        'title',
        'description',
        'type',
        'published_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get all published changelog entries
     * Only returns entries where published_at is not null and is in the past
     */
    public function getPublished(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE published_at IS NOT NULL 
                AND published_at <= NOW()
                ORDER BY version DESC, published_at DESC";
        return $this->query($sql, []);
    }

    /**
     * Get published changelog entries grouped by version
     */
    public function getPublishedGrouped(): array
    {
        $entries = $this->getPublished();
        $grouped = [];
        
        foreach ($entries as $entry) {
            $version = $entry['version'];
            if (!isset($grouped[$version])) {
                $grouped[$version] = [
                    'version' => $version,
                    'published_at' => $entry['published_at'],
                    'entries' => []
                ];
            }
            $grouped[$version]['entries'][] = $entry;
            
            // Keep the earliest published_at for this version
            if (strtotime($entry['published_at']) < strtotime($grouped[$version]['published_at'])) {
                $grouped[$version]['published_at'] = $entry['published_at'];
            }
        }
        
        return array_values($grouped);
    }

    /**
     * Get recent changelog entries
     */
    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE published_at IS NOT NULL 
                AND published_at <= NOW()
                ORDER BY published_at DESC, version DESC
                LIMIT :limit";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get all changelog entries (for admin)
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        return $this->query($sql, []);
    }

    /**
     * Get changelog statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN published_at IS NOT NULL AND published_at <= NOW() THEN 1 ELSE 0 END) as published,
                    SUM(CASE WHEN published_at IS NULL OR published_at > NOW() THEN 1 ELSE 0 END) as draft,
                    SUM(CASE WHEN type = 'feature' THEN 1 ELSE 0 END) as features,
                    SUM(CASE WHEN type = 'fix' THEN 1 ELSE 0 END) as fixes,
                    SUM(CASE WHEN type = 'improvement' THEN 1 ELSE 0 END) as improvements,
                    SUM(CASE WHEN type = 'security' THEN 1 ELSE 0 END) as security,
                    COUNT(DISTINCT version) as versions
                FROM {$this->table}";
        
        $result = $this->query($sql, []);
        return $result[0] ?? [
            'total' => 0,
            'published' => 0,
            'draft' => 0,
            'features' => 0,
            'fixes' => 0,
            'improvements' => 0,
            'security' => 0,
            'versions' => 0
        ];
    }

    /**
     * Create a new changelog entry
     */
    public function createEntry(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->create($data);
    }

    /**
     * Update a changelog entry
     */
    public function updateEntry(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->update($id, $data);
    }

    /**
     * Get distinct versions
     */
    public function getVersions(): array
    {
        $sql = "SELECT DISTINCT version FROM {$this->table} ORDER BY version DESC";
        $results = $this->query($sql, []);
        
        return array_column($results, 'version');
    }

    /**
     * Check if version exists
     */
    public function versionExists(string $version): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE version = :version";
        $result = $this->query($sql, ['version' => $version]);
        
        return (int)($result[0]['count'] ?? 0) > 0;
    }
}
