<?php
/**
 * MenuItem Model
 * Handles dynamic navigation menu items for bottom nav, bottom sheet, etc.
 */

class MenuItem extends BaseModel
{
    protected string $table = 'menu_items';
    
    protected array $fillable = [
        'label',
        'icon',
        'url',
        'position',
        'parent_id',
        'show_in_bottom_nav',
        'show_in_bottom_sheet',
        'sort_order',
        'is_active',
        'created_at',
        'updated_at'
    ];

    /**
     * Get items for bottom navigation bar
     */
    public function getBottomNavItems(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE show_in_bottom_nav = 1 AND is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Get items for bottom sheet menu
     */
    public function getBottomSheetItems(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE show_in_bottom_sheet = 1 AND is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Reorder menu items
     * 
     * @param array $items Array of [id => sort_order]
     */
    public function reorder(array $items): bool
    {
        $this->beginTransaction();
        
        try {
            foreach ($items as $id => $sortOrder) {
                $sql = "UPDATE {$this->table} SET sort_order = :sort_order, updated_at = NOW() WHERE id = :id";
                $this->execute($sql, ['sort_order' => (int) $sortOrder, 'id' => (int) $id]);
            }
            
            $this->commit();
            return true;
        } catch (Exception $e) {
            $this->rollback();
            throw $e;
        }
    }

    /**
     * Build a tree structure from menu items with parent_id
     */
    public function buildTree(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY sort_order ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        $items = $stmt->fetchAll();
        
        return $this->buildTreeFromItems($items);
    }

    /**
     * Recursively build tree from flat array of items
     */
    private function buildTreeFromItems(array $items, ?int $parentId = null): array
    {
        $tree = [];
        
        foreach ($items as $item) {
            $itemParentId = $item['parent_id'] !== null ? (int) $item['parent_id'] : null;
            
            if ($itemParentId === $parentId) {
                $children = $this->buildTreeFromItems($items, (int) $item['id']);
                if (!empty($children)) {
                    $item['children'] = $children;
                }
                $tree[] = $item;
            }
        }
        
        return $tree;
    }
}
