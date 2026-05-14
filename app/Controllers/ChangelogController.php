<?php
/**
 * Changelog Controller
 * Handles the public changelog/updates page
 */

class ChangelogController extends BaseController
{
    private Changelog $changelogModel;

    public function __construct()
    {
        $this->changelogModel = new Changelog();
    }

    /**
     * Show the public changelog page
     */
    public function index(): void
    {
        // Get published changelog entries grouped by version
        $changelogs = $this->changelogModel->getPublishedGrouped();
        
        // Get recent entries for a highlight section
        $recentEntries = $this->changelogModel->getRecent(5);

        // Render changelog page with landing layout (no auth required)
        $this->renderLanding('changelog/index', [
            'pageTitle' => 'Changelog - Hay API Gateway',
            'changelogs' => $changelogs,
            'recentEntries' => $recentEntries
        ], ['changelog'], []);
    }

    /**
     * Render a page view within the landing layout (no sidebar)
     *
     * @param string $view Path to the view (relative to views/pages/)
     * @param array $data Data to pass to the view
     * @param array $pageCssFiles Array of CSS file names (without .css extension)
     * @param array $pageJsFiles Array of JS file names (without .js extension)
     * @return void
     */
    protected function renderLanding(string $view, array $data = [], array $pageCssFiles = [], array $pageJsFiles = []): void
    {
        // Extract data for use in views
        extract($data);
        
        // Store the content view path
        $contentView = VIEWS_PATH . '/pages/' . $view . '.php';
        
        // Include the landing layout
        require VIEWS_PATH . '/layouts/landing_layout.php';
    }
}
