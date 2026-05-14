<?php
/**
 * Menu Service
 * Handles navigation menu items with caching
 */

class MenuService
{
    private MenuItem $menuItemModel;
    private static array $cache = [];

    public function __construct(MenuItem $menuItemModel)
    {
        $this->menuItemModel = $menuItemModel;
    }

    /**
     * Get cached bottom navigation items
     */
    public function getBottomNavItems(): array
    {
        $cacheKey = 'bottom_nav_items';
        
        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = $this->menuItemModel->getBottomNavItems();
        }
        
        return self::$cache[$cacheKey];
    }

    /**
     * Get cached bottom sheet items
     */
    public function getBottomSheetItems(): array
    {
        $cacheKey = 'bottom_sheet_items';
        
        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = $this->menuItemModel->getBottomSheetItems();
        }
        
        return self::$cache[$cacheKey];
    }

    /**
     * Get full menu as a tree structure
     */
    public function getFullMenu(): array
    {
        $cacheKey = 'full_menu_tree';
        
        if (!isset(self::$cache[$cacheKey])) {
            self::$cache[$cacheKey] = $this->menuItemModel->buildTree();
        }
        
        return self::$cache[$cacheKey];
    }

    /**
     * Get a menu item by ID
     */
    public function getItemById(int $id): ?array
    {
        return $this->menuItemModel->find($id);
    }

    /**
     * Clear the menu cache
     */
    public function clearCache(): void
    {
        self::$cache = [];
    }

    /**
     * Reorder menu items
     */
    public function reorder(array $items): bool
    {
        $result = $this->menuItemModel->reorder($items);
        $this->clearCache();
        return $result;
    }
}
