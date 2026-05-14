<?php
/**
 * Rate Limit Service
 * Handles rate limiting for API requests and login attempts
 */

class RateLimitService
{
    private string $storageDir;
    private array $cache = [];

    public function __construct()
    {
        $this->storageDir = dirname(CONFIG_PATH) . '/storage/rate_limits';

        // Ensure storage directory exists
        if (!is_dir($this->storageDir)) {
            @mkdir($this->storageDir, 0755, true);
        }
    }

    /**
     * Check if a request is within rate limit
     */
    public function check(string $key, int $limit, int $window): bool
    {
        $data = $this->getData($key);

        // Clean up expired entries
        $now = time();
        $data['attempts'] = array_filter($data['attempts'], function ($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });

        $this->saveData($key, $data);

        // Check if under limit
        return count($data['attempts']) < $limit;
    }

    /**
     * Increment the counter for a key
     */
    public function increment(string $key): int
    {
        $data = $this->getData($key);
        $data['attempts'][] = time();
        $this->saveData($key, $data);

        return count($data['attempts']);
    }

    /**
     * Get remaining attempts for a key
     */
    public function getRemainingAttempts(string $key, int $limit, int $window): int
    {
        $data = $this->getData($key);

        // Clean up expired entries
        $now = time();
        $data['attempts'] = array_filter($data['attempts'], function ($timestamp) use ($now, $window) {
            return ($now - $timestamp) < $window;
        });

        $used = count($data['attempts']);
        return max(0, $limit - $used);
    }

    /**
     * Reset the rate limit for a key
     */
    public function reset(string $key): bool
    {
        $file = $this->getFilePath($key);

        if (isset($this->cache[$key])) {
            unset($this->cache[$key]);
        }

        if (file_exists($file)) {
            return unlink($file);
        }

        return true;
    }

    /**
     * Check if a key is blocked (exceeded rate limit)
     */
    public function isBlocked(string $key, int $limit, int $window): bool
    {
        return !$this->check($key, $limit, $window);
    }

    /**
     * Get time until rate limit resets
     */
    public function getResetTime(string $key, int $window): int
    {
        $data = $this->getData($key);

        if (empty($data['attempts'])) {
            return 0;
        }

        $oldestAttempt = min($data['attempts']);
        $resetTime = $oldestAttempt + $window;

        return max(0, $resetTime - time());
    }

    /**
     * Get rate limit data for a key
     */
    private function getData(string $key): array
    {
        // Check cache first
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $file = $this->getFilePath($key);

        if (!file_exists($file)) {
            return ['attempts' => []];
        }

        $content = file_get_contents($file);
        $data = json_decode($content, true);

        if (!$data || !isset($data['attempts'])) {
            return ['attempts' => []];
        }

        $this->cache[$key] = $data;
        return $data;
    }

    /**
     * Save rate limit data for a key
     */
    private function saveData(string $key, array $data): bool
    {
        $this->cache[$key] = $data;
        $file = $this->getFilePath($key);

        return file_put_contents($file, json_encode($data), LOCK_EX) !== false;
    }

    /**
     * Get file path for a rate limit key
     */
    private function getFilePath(string $key): string
    {
        // Sanitize key for filename
        $safeKey = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $key);
        return $this->storageDir . '/' . $safeKey . '.json';
    }

    /**
     * Clean up expired rate limit files
     */
    public function cleanup(int $maxAge = 86400): int
    {
        $cleaned = 0;
        $now = time();

        $files = glob($this->storageDir . '/*.json');

        foreach ($files as $file) {
            if (filemtime($file) < ($now - $maxAge)) {
                unlink($file);
                $cleaned++;
            }
        }

        return $cleaned;
    }
}
