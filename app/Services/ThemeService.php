<?php
/**
 * Theme Service
 * Handles theme management and user preferences
 */

class ThemeService
{
    private Theme $themeModel;
    private User $userModel;

    public function __construct(Theme $themeModel, User $userModel)
    {
        $this->themeModel = $themeModel;
        $this->userModel = $userModel;
    }

    /**
     * Get the current theme for a user (or default if not set)
     */
    public function getCurrentTheme(int $userId): array
    {
        $user = $this->userModel->find($userId);
        
        if ($user && !empty($user['preferred_theme'])) {
            $theme = $this->themeModel->findByName($user['preferred_theme']);
            if ($theme) {
                return $theme;
            }
        }
        
        // Fall back to default theme
        $defaultTheme = $this->themeModel->findDefault();
        
        if ($defaultTheme) {
            return $defaultTheme;
        }
        
        // Final fallback - light theme
        return $this->themeModel->findByName('light') ?? [
            'name' => 'light',
            'css_variables' => '{}'
        ];
    }

    /**
     * Set the preferred theme for a user
     */
    public function setTheme(int $userId, string $themeName): ?array
    {
        $theme = $this->themeModel->findByName($themeName);
        
        if (!$theme) {
            return null;
        }
        
        // Update user preference
        $sql = "UPDATE users SET preferred_theme = :theme, updated_at = NOW() WHERE id = :id";
        $this->userModel->execute($sql, ['theme' => $themeName, 'id' => $userId]);
        
        return $theme;
    }

    /**
     * Get CSS variables for a theme by name
     */
    public function getThemeCssVariables(string $themeName): array
    {
        $theme = $this->themeModel->findByName($themeName);
        
        if (!$theme) {
            return [];
        }
        
        $cssVariables = $theme['css_variables'];
        
        if (is_string($cssVariables)) {
            $decoded = json_decode($cssVariables, true);
            return $decoded ?: [];
        }
        
        return is_array($cssVariables) ? $cssVariables : [];
    }

    /**
     * Get all available themes
     */
    public function getAllThemes(): array
    {
        return $this->themeModel->findActive();
    }
}
