<?php
/**
 * Theme Controller
 * Handles theme switching and CSS variable endpoints
 */

class ThemeController extends BaseController
{
    private ThemeService $themeService;
    private AuthService $authService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->themeService = new ThemeService(new Theme(), $userModel);
    }

    /**
     * POST /theme/set - AJAX endpoint to save theme preference
     */
    public function set(): void
    {
        $user = $this->authService->user();
        $data = json_decode(file_get_contents('php://input'), true);
        $theme = $data['theme'] ?? 'light';

        if (!in_array($theme, ['light', 'dark'])) {
            $this->json(['success' => false, 'error' => 'Invalid theme'], 400);
            return;
        }

        if ($user) {
            $this->themeService->setTheme($user['id'], $theme);
        }

        $this->json(['success' => true, 'theme' => $theme]);
    }

    /**
     * GET /theme/variables - Returns CSS variables for current theme
     */
    public function getVariables(): void
    {
        $theme = $_GET['theme'] ?? 'light';
        $variables = $this->themeService->getThemeCssVariables($theme);
        $this->json(['variables' => $variables]);
    }
}
