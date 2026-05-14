<?php
/**
 * Session Service
 * Handles PHP session management with secure defaults
 */

class SessionService
{
    private bool $started = false;

    /**
     * Start the session with secure settings
     */
    public function start(): bool
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return true;
        }

        $config = require CONFIG_PATH . '/app.php';
        $sessionConfig = $config['session'] ?? [];

        // Configure session settings
        ini_set('session.cookie_httponly', $sessionConfig['http_only'] ?? true);
        ini_set('session.cookie_secure', $sessionConfig['secure'] ?? false);
        ini_set('session.cookie_samesite', $sessionConfig['same_site'] ?? 'lax');
        ini_set('session.gc_maxlifetime', ($sessionConfig['lifetime'] ?? 120) * 60);
        ini_set('session.use_strict_mode', true);

        $this->started = session_start();
        return $this->started;
    }

    /**
     * Set a session value
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Get a session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Check if session has a key
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove a session value
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Set a flash message (available only for next request)
     */
    public function flash(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get and remove a flash message
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Destroy the session completely
     */
    public function destroy(): bool
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];

            // Delete session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            $this->started = false;
            return session_destroy();
        }

        return true;
    }

    /**
     * Regenerate session ID (for security after login)
     */
    public function regenerate(bool $deleteOldSession = true): bool
    {
        $this->ensureStarted();
        return session_regenerate_id($deleteOldSession);
    }

    /**
     * Ensure session is started
     */
    private function ensureStarted(): void
    {
        if (!$this->started && session_status() !== PHP_SESSION_ACTIVE) {
            $this->start();
        }
    }
}
