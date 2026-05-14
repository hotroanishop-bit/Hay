<?php
/**
 * Base Controller
 * Provides common functionality for all controllers
 */
class BaseController
{
    /**
     * Current page identifier for sidebar highlighting
     * @var string
     */
    protected $currentPage = '';

    /**
     * Render a partial view without the master layout
     *
     * @param string $partial Path to the partial view (relative to views/)
     * @param array $data Data to pass to the view
     * @return void
     */
    protected function renderPartial(string $partial, array $data = []): void
    {
        extract($data);
        $partialPath = VIEWS_PATH . '/' . $partial . '.php';
        
        if (file_exists($partialPath)) {
            require $partialPath;
        }
    }

    /**
     * Render a page view within the master layout
     *
     * @param string $view Path to the view (relative to views/pages/)
     * @param array $data Data to pass to the view
     * @param array $pageCssFiles Array of CSS file names (without .css extension)
     * @param array $pageJsFiles Array of JS file names (without .js extension)
     * @return void
     */
    protected function render(string $view, array $data = [], array $pageCssFiles = [], array $pageJsFiles = []): void
    {
        // Extract data for use in views
        extract($data);
        
        // Set current page for sidebar highlighting
        $currentPage = $this->currentPage;
        
        // Store the content view path
        $contentView = VIEWS_PATH . '/pages/' . $view . '.php';
        
        // Include the master layout
        require VIEWS_PATH . '/layouts/master_layout.php';
    }

    /**
     * Redirect to a URL
     *
     * @param string $url The URL to redirect to
     * @return void
     */
    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Set a flash message in the session
     *
     * @param string $type Message type (success, error, warning, info)
     * @param string $message The message content
     * @return void
     */
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = [
            'type' => $type,
            'message' => $message
        ];
    }

    /**
     * Return a JSON response
     *
     * @param array $data Data to encode as JSON
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function json(array $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Return a 404 Not Found response
     *
     * @return void
     */
    protected function notFound(): void
    {
        http_response_code(404);
        $this->render('errors/404', [
            'pageTitle' => 'Page Not Found',
            'currentPage' => ''
        ]);
    }
}
