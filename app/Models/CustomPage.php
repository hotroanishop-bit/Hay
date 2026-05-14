<?php
/**
 * CustomPage Model
 * Handles CMS-style custom pages with SEO support
 */

class CustomPage extends BaseModel
{
    protected string $table = 'custom_pages';
    
    protected array $fillable = [
        'title',
        'slug',
        'content',
        'meta_description',
        'is_published',
        'menu_order',
        'show_in_menu',
        'created_at',
        'updated_at'
    ];

    /**
     * Find a published page by slug
     */
    public function findBySlug(string $slug): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE slug = :slug AND is_published = 1 LIMIT 1";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch();
        
        return $result ?: null;
    }

    /**
     * Find all published pages ordered by menu_order
     */
    public function findPublished(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_published = 1 ORDER BY menu_order ASC, title ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }

    /**
     * Find pages that should appear in menu
     */
    public function findForMenu(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_published = 1 AND show_in_menu = 1 ORDER BY menu_order ASC, title ASC";
        $stmt = $this->db()->prepare($sql);
        $stmt->execute();
        
        return $stmt->fetchAll();
    }
}
