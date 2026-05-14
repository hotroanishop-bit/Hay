<?php
/**
 * CAPTCHA Service
 * Simple math-based CAPTCHA for security
 */

class CaptchaService
{
    private const SESSION_KEY = 'captcha_answer';
    private const ATTEMPTS_KEY = 'captcha_attempts';

    /**
     * Generate a new CAPTCHA question
     * @return array ['question' => string, 'id' => string]
     */
    public function generateCaptcha(): array
    {
        $operations = ['+', '-', '*'];
        $operation = $operations[array_rand($operations)];
        
        // Generate appropriate numbers based on operation
        switch ($operation) {
            case '*':
                $num1 = rand(2, 10);
                $num2 = rand(2, 5);
                $answer = $num1 * $num2;
                break;
            case '-':
                $num1 = rand(5, 20);
                $num2 = rand(1, $num1 - 1);
                $answer = $num1 - $num2;
                break;
            default: // +
                $num1 = rand(1, 15);
                $num2 = rand(1, 15);
                $answer = $num1 + $num2;
        }

        // Create a unique ID for this captcha
        $captchaId = bin2hex(random_bytes(8));
        
        // Store answer in session
        $_SESSION[self::SESSION_KEY] = [
            'id' => $captchaId,
            'answer' => $answer,
            'expires' => time() + 300 // 5 minutes
        ];

        // Format operation for display
        $displayOp = $operation === '*' ? 'x' : $operation;
        
        return [
            'question' => "What is {$num1} {$displayOp} {$num2}?",
            'id' => $captchaId
        ];
    }

    /**
     * Verify a CAPTCHA answer
     */
    public function verifyCaptcha(string $answer, ?string $captchaId = null): bool
    {
        $stored = $_SESSION[self::SESSION_KEY] ?? null;
        
        if (!$stored) {
            return false;
        }

        // Check expiration
        if (time() > ($stored['expires'] ?? 0)) {
            unset($_SESSION[self::SESSION_KEY]);
            return false;
        }

        // Check ID if provided
        if ($captchaId !== null && $captchaId !== ($stored['id'] ?? '')) {
            return false;
        }

        // Compare answer
        $isCorrect = (int)$answer === (int)$stored['answer'];
        
        // Clear captcha after verification attempt
        unset($_SESSION[self::SESSION_KEY]);
        
        return $isCorrect;
    }

    /**
     * Check if CAPTCHA is enabled globally
     */
    public function isEnabled(): bool
    {
        // Check settings - default to enabled
        $settings = $this->getSettings();
        return (bool)($settings['captcha_enabled'] ?? true);
    }

    /**
     * Check if CAPTCHA is required for a specific user/IP based on failed attempts
     */
    public function isRequired(int $failedAttempts): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }
        
        // Require CAPTCHA after 3 failed attempts
        $threshold = $this->getSettings()['captcha_threshold'] ?? 3;
        return $failedAttempts >= $threshold;
    }

    /**
     * Get current captcha question if one exists
     */
    public function getCurrentCaptcha(): ?array
    {
        $stored = $_SESSION[self::SESSION_KEY] ?? null;
        
        if (!$stored || time() > ($stored['expires'] ?? 0)) {
            return null;
        }
        
        // Re-generate question from stored data
        return [
            'id' => $stored['id'],
            'question' => 'Please solve the math problem above'
        ];
    }

    /**
     * Get CAPTCHA settings from config
     */
    private function getSettings(): array
    {
        static $settings = null;
        
        if ($settings === null) {
            $settingsFile = CONFIG_PATH . '/settings.php';
            if (file_exists($settingsFile)) {
                $allSettings = require $settingsFile;
                $settings = $allSettings['security'] ?? [];
            } else {
                $settings = [];
            }
        }
        
        return $settings;
    }

    /**
     * Track failed CAPTCHA attempts
     */
    public function trackFailedAttempt(): void
    {
        $attempts = $_SESSION[self::ATTEMPTS_KEY] ?? 0;
        $_SESSION[self::ATTEMPTS_KEY] = $attempts + 1;
    }

    /**
     * Get failed CAPTCHA attempt count
     */
    public function getFailedAttempts(): int
    {
        return $_SESSION[self::ATTEMPTS_KEY] ?? 0;
    }

    /**
     * Reset failed attempts counter
     */
    public function resetFailedAttempts(): void
    {
        unset($_SESSION[self::ATTEMPTS_KEY]);
    }
}
