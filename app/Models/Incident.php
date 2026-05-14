<?php
/**
 * Incident Model
 * Handles status page incident data operations
 */

class Incident extends BaseModel
{
    protected string $table = 'incidents';
    
    protected array $fillable = [
        'title',
        'description',
        'status',
        'severity',
        'affected_components',
        'started_at',
        'resolved_at',
        'created_at',
        'updated_at'
    ];

    /**
     * Get active incidents (not resolved)
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status != 'resolved' 
                ORDER BY 
                    CASE severity 
                        WHEN 'critical' THEN 1 
                        WHEN 'major' THEN 2 
                        WHEN 'minor' THEN 3 
                    END,
                    started_at DESC";
        return $this->query($sql, []);
    }

    /**
     * Get recent incidents with optional limit
     */
    public function getRecent(int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} 
                ORDER BY started_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get resolved incidents
     */
    public function getResolved(int $limit = 20): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE status = 'resolved' 
                ORDER BY resolved_at DESC 
                LIMIT :limit";
        
        $stmt = $this->db()->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Create a new incident
     */
    public function createIncident(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (empty($data['started_at'])) {
            $data['started_at'] = date('Y-m-d H:i:s');
        }
        
        if (!empty($data['affected_components']) && is_array($data['affected_components'])) {
            $data['affected_components'] = json_encode($data['affected_components']);
        }
        
        return $this->create($data);
    }

    /**
     * Update an incident
     */
    public function updateIncident(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        if (!empty($data['affected_components']) && is_array($data['affected_components'])) {
            $data['affected_components'] = json_encode($data['affected_components']);
        }
        
        return $this->update($id, $data);
    }

    /**
     * Resolve an incident
     */
    public function resolve(int $id): bool
    {
        $sql = "UPDATE {$this->table} 
                SET status = 'resolved', 
                    resolved_at = NOW(), 
                    updated_at = NOW() 
                WHERE id = :id";
        return $this->execute($sql, ['id' => $id]);
    }

    /**
     * Get incident statistics
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status != 'resolved' THEN 1 ELSE 0 END) as active,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
                    SUM(CASE WHEN severity = 'critical' AND status != 'resolved' THEN 1 ELSE 0 END) as critical,
                    SUM(CASE WHEN severity = 'major' AND status != 'resolved' THEN 1 ELSE 0 END) as major,
                    SUM(CASE WHEN severity = 'minor' AND status != 'resolved' THEN 1 ELSE 0 END) as minor
                FROM {$this->table}";
        
        $result = $this->query($sql, []);
        return $result[0] ?? [
            'total' => 0,
            'active' => 0,
            'resolved' => 0,
            'critical' => 0,
            'major' => 0,
            'minor' => 0
        ];
    }

    /**
     * Get all incidents for admin
     */
    public function getAll(): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        return $this->query($sql, []);
    }

    /**
     * Check if there are any active incidents
     */
    public function hasActiveIncidents(): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE status != 'resolved'";
        $result = $this->query($sql, []);
        return (int)($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Get highest severity of active incidents
     */
    public function getHighestActiveSeverity(): ?string
    {
        $sql = "SELECT severity FROM {$this->table} 
                WHERE status != 'resolved' 
                ORDER BY 
                    CASE severity 
                        WHEN 'critical' THEN 1 
                        WHEN 'major' THEN 2 
                        WHEN 'minor' THEN 3 
                    END
                LIMIT 1";
        $result = $this->query($sql, []);
        return $result[0]['severity'] ?? null;
    }

    /**
     * Add incident update
     */
    public function addUpdate(int $incidentId, string $message, string $status): int
    {
        $sql = "INSERT INTO incident_updates (incident_id, message, status, created_at) 
                VALUES (:incident_id, :message, :status, NOW())";
        
        $this->execute($sql, [
            'incident_id' => $incidentId,
            'message' => $message,
            'status' => $status
        ]);
        
        return (int) $this->db()->lastInsertId();
    }

    /**
     * Get incident updates
     */
    public function getUpdates(int $incidentId): array
    {
        $sql = "SELECT * FROM incident_updates 
                WHERE incident_id = :incident_id 
                ORDER BY created_at DESC";
        return $this->query($sql, ['incident_id' => $incidentId]);
    }
}
