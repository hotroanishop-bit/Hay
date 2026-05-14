<?php
/**
 * Auth Service
 * Handles user authentication, registration, and session management
 */

class AuthService
{
    private SessionService $session;
    private User $userModel;
    private ?SessionManagementService $sessionManagement = null;

    private const MAX_FAILED_ATTEMPTS = 5;
    private const LOCKOUT_MINUTES = 15;

    public function __construct(SessionService $session, User $userModel)
    {
        $this->session = $session;
        $this->userModel = $userModel;
    }

    /**
     * Get session management service (lazy load)
     */
    private function getSessionManagement(): SessionManagementService
    {
        if ($this->sessionManagement === null) {
            $this->sessionManagement = new SessionManagementService();
        }
        return $this->sessionManagement;
    }

    /**
     * Attempt to authenticate a user with email and password
     */
    public function attempt(string $email, string $password): bool
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Check if account is locked
        if ($this->isAccountLocked($user)) {
            $this->logLoginAttempt($user['id'], false);
            return false;
        }

        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            // Increment failed attempts
            $this->incrementFailedAttempts($user['id']);
            $this->logLoginAttempt($user['id'], false);
            return false;
        }

        // Reset failed attempts on successful login
        $this->resetFailedAttempts($user['id']);

        // Check if 2FA is enabled - if so, don't fully log in yet
        if (!empty($user['two_factor_enabled'])) {
            $this->session->set('pending_2fa_user_id', $user['id']);
            return true;
        }

        // Complete the login
        $this->completeLogin($user);

        return true;
    }

    /**
     * Complete the login process after all verification
     */
    private function completeLogin(array $user): void
    {
        // Regenerate session ID to prevent session fixation
        $this->session->regenerate();
        $this->session->set('user_id', $user['id']);
        $this->session->set('logged_in_at', time());

        // Create a persistent session token for session management
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
        
        try {
            $sessionToken = $this->getSessionManagement()->createSession($user['id'], $ip, $userAgent);
            $this->session->set('session_token', $sessionToken);
        } catch (Exception $e) {
            // Session management is optional, continue without it
        }

        // Log successful login
        $this->logLoginAttempt($user['id'], true);
    }

    /**
     * Log a login attempt
     */
    private function logLoginAttempt(int $userId, bool $success): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;

        try {
            $this->getSessionManagement()->logLogin($userId, $ip, $userAgent, $success);
        } catch (Exception $e) {
            // Session management is optional, continue without logging
        }
    }

    /**
     * Check if an account is locked
     */
    public function isAccountLocked(array $user): bool
    {
        if (empty($user['locked_until'])) {
            return false;
        }

        $lockedUntil = strtotime($user['locked_until']);
        if ($lockedUntil > time()) {
            return true;
        }

        // Lock has expired, reset it
        $this->resetFailedAttempts($user['id']);
        return false;
    }

    /**
     * Get time remaining until account unlock
     */
    public function getUnlockTime(array $user): ?int
    {
        if (empty($user['locked_until'])) {
            return null;
        }

        $lockedUntil = strtotime($user['locked_until']);
        $remaining = $lockedUntil - time();

        return $remaining > 0 ? $remaining : null;
    }

    /**
     * Increment failed login attempts
     */
    private function incrementFailedAttempts(int $userId): void
    {
        $user = $this->userModel->find($userId);
        $attempts = ($user['failed_login_attempts'] ?? 0) + 1;

        $data = ['failed_login_attempts' => $attempts];

        // Lock account after max attempts
        if ($attempts >= self::MAX_FAILED_ATTEMPTS) {
            $data['locked_until'] = date('Y-m-d H:i:s', strtotime('+' . self::LOCKOUT_MINUTES . ' minutes'));
        }

        $this->userModel->update($userId, $data);
    }

    /**
     * Reset failed login attempts
     */
    private function resetFailedAttempts(int $userId): void
    {
        $this->userModel->update($userId, [
            'failed_login_attempts' => 0,
            'locked_until' => null
        ]);
    }

    /**
     * Get failed login attempts for a user
     */
    public function getFailedAttempts(string $email): int
    {
        $user = $this->userModel->findByEmail($email);
        return $user['failed_login_attempts'] ?? 0;
    }

    /**
     * Register a new user
     */
    public function register(array $data): int
    {
        // Check if email already exists
        $existing = $this->userModel->findByEmail($data['email']);
        if ($existing) {
            throw new Exception('Email already registered');
        }

        // Create user with hashed password
        $userId = $this->userModel->createUser([
            'email' => $data['email'],
            'password' => $data['password'],
            'name' => $data['name'] ?? null,
            'balance' => 0.00,
            'is_admin' => 0,
            'two_factor_enabled' => 0,
        ]);

        return $userId;
    }

    /**
     * Log out the current user
     */
    public function logout(): void
    {
        // Terminate the session token if exists
        $sessionToken = $this->session->get('session_token');
        $userId = $this->session->get('user_id');
        
        if ($sessionToken && $userId) {
            try {
                $session = $this->getSessionManagement()->getSessionByToken($sessionToken);
                if ($session) {
                    $this->getSessionManagement()->terminateSession($session['id'], $userId);
                }
            } catch (Exception $e) {
                // Continue with logout even if session termination fails
            }
        }

        $this->session->destroy();
    }

    /**
     * Get the currently authenticated user
     */
    public function user(): ?array
    {
        $userId = $this->session->get('user_id');

        if (!$userId) {
            return null;
        }

        return $this->userModel->find($userId);
    }

    /**
     * Check if a user is authenticated
     */
    public function check(): bool
    {
        return $this->session->has('user_id');
    }

    /**
     * Get the authenticated user's ID
     */
    public function id(): ?int
    {
        return $this->session->get('user_id');
    }

    /**
     * Verify 2FA code for pending authentication
     */
    public function verify2FA(string $code): bool
    {
        $pendingUserId = $this->session->get('pending_2fa_user_id');

        if (!$pendingUserId) {
            return false;
        }

        $user = $this->userModel->find($pendingUserId);
        if (!$user || empty($user['two_factor_secret'])) {
            return false;
        }

        // Verify TOTP code with drift tolerance
        if (!$this->verifyTOTP($user['two_factor_secret'], $code)) {
            return false;
        }

        // Complete the login
        $this->session->remove('pending_2fa_user_id');
        $this->completeLogin($user);

        return true;
    }

    /**
     * Generate a password reset token
     */
    public function generatePasswordReset(string $email): ?string
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return null;
        }

        // Generate a secure random token
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Store the token hash (in production, store in a password_resets table)
        $this->session->set('password_reset_' . $user['id'], [
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt
        ]);

        return $token;
    }

    /**
     * Reset password using a valid token
     */
    public function resetPassword(string $token, string $email, string $newPassword): bool
    {
        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            return false;
        }

        // Verify token
        $resetData = $this->session->get('password_reset_' . $user['id']);
        if (!$resetData) {
            return false;
        }

        $tokenHash = hash('sha256', $token);
        if ($tokenHash !== $resetData['token_hash']) {
            return false;
        }

        // Check expiration
        if (strtotime($resetData['expires_at']) < time()) {
            $this->session->remove('password_reset_' . $user['id']);
            return false;
        }

        // Update password
        $newPasswordHash = $this->userModel->hashPassword($newPassword);
        $this->userModel->update($user['id'], [
            'password_hash' => $newPasswordHash,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Clear reset token
        $this->session->remove('password_reset_' . $user['id']);

        return true;
    }

    /**
     * Check if 2FA verification is pending
     */
    public function isPending2FA(): bool
    {
        return $this->session->has('pending_2fa_user_id');
    }

    /**
     * Generate TOTP code for a specific time window
     */
    private function generateTOTPForTime(string $secret, int $time): string
    {
        $hash = hash_hmac('sha1', pack('N*', 0) . pack('N*', $time), base64_decode($secret), true);
        $offset = ord(substr($hash, -1)) & 0x0F;
        $code = (
            ((ord($hash[$offset]) & 0x7F) << 24) |
            ((ord($hash[$offset + 1]) & 0xFF) << 16) |
            ((ord($hash[$offset + 2]) & 0xFF) << 8) |
            (ord($hash[$offset + 3]) & 0xFF)
        ) % 1000000;

        return str_pad((string) $code, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generate TOTP code (current window)
     */
    private function generateTOTP(string $secret): string
    {
        $time = floor(time() / 30);
        return $this->generateTOTPForTime($secret, $time);
    }

    /**
     * Verify TOTP code with drift tolerance (checks current, previous, and next window)
     */
    private function verifyTOTP(string $secret, string $code): bool
    {
        $currentTime = floor(time() / 30);
        
        // Check current window and +/- 1 window for clock drift tolerance (30-60 seconds)
        for ($offset = -1; $offset <= 1; $offset++) {
            $expectedCode = $this->generateTOTPForTime($secret, $currentTime + $offset);
            if ($code === $expectedCode) {
                return true;
            }
        }
        
        return false;
    }
}
