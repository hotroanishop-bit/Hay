<?php
/**
 * Custom Page Service
 * Handles CMS-style custom pages
 */

class CustomPageService
{
    private CustomPage $customPageModel;

    public function __construct(CustomPage $customPageModel)
    {
        $this->customPageModel = $customPageModel;
    }

    /**
     * Get a published page by slug
     */
    public function getPage(string $slug): ?array
    {
        return $this->customPageModel->findBySlug($slug);
    }

    /**
     * Get all published pages
     */
    public function getPublishedPages(): array
    {
        return $this->customPageModel->findPublished();
    }

    /**
     * Get pages that should appear in the menu
     */
    public function getMenuPages(): array
    {
        return $this->customPageModel->findForMenu();
    }

    /**
     * Create a new custom page
     */
    public function createPage(array $data): int
    {
        $data['created_at'] = date('Y-m-d H:i:s');
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->customPageModel->create($data);
    }

    /**
     * Update a custom page
     */
    public function updatePage(int $id, array $data): bool
    {
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        return $this->customPageModel->update($id, $data);
    }

    /**
     * Delete a custom page
     */
    public function deletePage(int $id): bool
    {
        return $this->customPageModel->delete($id);
    }
}
