<?php
/**
 * Landing Controller
 * Handles the public landing page for non-authenticated visitors
 */

class LandingController extends BaseController
{
    private Plan $planModel;

    public function __construct()
    {
        $this->planModel = new Plan();
    }

    /**
     * Show the landing page or redirect to dashboard if logged in
     */
    public function index(): void
    {
        // If user is authenticated, redirect to dashboard
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
            return;
        }

        // Get active plans for pricing section
        $plans = $this->planModel->findActive();

        // Render landing page with minimal layout
        $this->renderLanding('landing/index', [
            'pageTitle' => 'Hay API Gateway - Powerful AI API Management',
            'plans' => $plans
        ], ['landing'], ['landing']);
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
