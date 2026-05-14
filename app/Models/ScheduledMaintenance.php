<?php
/**
 * Scheduled Maintenance Model
 * Handles scheduled maintenance window operations
 */

class ScheduledMaintenance extends BaseModel
{
    protected string $table = 'scheduled_maintenance';
    protected array $fillable = [
        'title',
        'message',
        'starts_at',
        'ends_at',
        'is_active',
        'show_countdown'
    ];

    /**
     * Get the currently active maintenance window
     * Returns the maintenance record if current time is within a scheduled window
     */
    public function getActive(): ?array
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND starts_at <= :now 
                AND ends_at >= :now2 
                ORDER BY starts_at ASC 
                LIMIT 1";
        
        $results = $this->query($sql, ['now' => $now, 'now2' => $now]);
        return $results[0] ?? null;
    }

    /**
     * Get upcoming scheduled maintenance windows
     * Returns maintenance windows that haven't started yet
     */
    public function getUpcoming(int $limit = 5): array
    {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT * FROM {$this->table} 
                WHERE is_active = 1 
                AND starts_at > :now 
                ORDER BY starts_at ASC 
                LIMIT {$limit}";
        
        return $this->query($sql, ['now' => $now]);
    }

    /**
     * Get the next upcoming maintenance window
     */
    public function getNextUpcoming(): ?array
    {
        $upcoming = $this->getUpcoming(1);
        return $upcoming[0] ?? null;
    }

    /**
     * Get all maintenance windows with pagination
     */
    public function getAll(int $page = 1, int $perPage = 20): array
    {
        $offset = ($page - 1) * $perPage;
        
        $countSql = "SELECT COUNT(*) as total FROM {$this->table}";
        $countResult = $this->query($countSql, []);
        $total = (int) ($countResult[0]['total'] ?? 0);
        
        $sql = "SELECT * FROM {$this->table} ORDER BY starts_at DESC LIMIT {$perPage} OFFSET {$offset}";
        $items = $this->query($sql, []);
        
        return [
            'items' => $items,
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'total_pages' => (int) ceil($total / $perPage)
        ];
    }

    /**
     * Check if current time is within a maintenance window
     */
    public function isWithinWindow(array $maintenance): bool
    {
        $now = time();
        $startTime = strtotime($maintenance['starts_at']);
        $endTime = strtotime($maintenance['ends_at']);
        
        return $now >= $startTime && $now <= $endTime;
    }

    /**
     * Toggle the active status of a maintenance window
     */
    public function toggleActive(int $id): bool
    {
        $maintenance = $this->find($id);
        if (!$maintenance) {
            return false;
        }
        
        $newStatus = $maintenance['is_active'] ? 0 : 1;
        return $this->update($id, ['is_active' => $newStatus]);
    }

    /**
     * Get maintenance windows by status
     */
    public function getByStatus(string $status): array
    {
        $now = date('Y-m-d H:i:s');
        
        switch ($status) {
            case 'active':
                $sql = "SELECT * FROM {$this->table} 
                        WHERE is_active = 1 
                        AND starts_at <= :now 
                        AND ends_at >= :now2 
                        ORDER BY starts_at ASC";
                return $this->query($sql, ['now' => $now, 'now2' => $now]);
                
            case 'upcoming':
                $sql = "SELECT * FROM {$this->table} 
                        WHERE is_active = 1 
                        AND starts_at > :now 
                        ORDER BY starts_at ASC";
                return $this->query($sql, ['now' => $now]);
                
            case 'past':
                $sql = "SELECT * FROM {$this->table} 
                        WHERE ends_at < :now 
                        ORDER BY ends_at DESC";
                return $this->query($sql, ['now' => $now]);
                
            default:
                return $this->findAll([], 'starts_at DESC');
        }
    }

    /**
     * Check if there's any overlapping maintenance window
     */
    public function hasOverlap(string $startsAt, string $endsAt, ?int $excludeId = null): bool
    {
        $params = [
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'starts_at2' => $startsAt,
            'ends_at2' => $endsAt
        ];
        
        $excludeClause = '';
        if ($excludeId !== null) {
            $excludeClause = 'AND id != :exclude_id';
            $params['exclude_id'] = $excludeId;
        }
        
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE is_active = 1 
                AND (
                    (starts_at <= :starts_at AND ends_at >= :starts_at2)
                    OR (starts_at <= :ends_at AND ends_at >= :ends_at2)
                    OR (starts_at >= :starts_at AND ends_at <= :ends_at)
                )
                {$excludeClause}";
        
        // Fix parameter binding for SQL
        $sql = str_replace(':starts_at2', ':starts_at', $sql);
        $sql = str_replace(':ends_at2', ':ends_at', $sql);
        unset($params['starts_at2'], $params['ends_at2']);
        
        $result = $this->query($sql, $params);
        return ($result[0]['count'] ?? 0) > 0;
    }
}
