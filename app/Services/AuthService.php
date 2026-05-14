<?php
/**
 * Auth Service
 * Handles user authentication, registration, and session management
 */

class AuthService
{
    private SessionService $session;
    private User $userModel;

    public function __construct(SessionService $session, User $userModel)
    {
        $this->session = $session;
        $this->userModel = $userModel;
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

        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            return false;
        }

        // Check if 2FA is enabled - if so, don't fully log in yet
        if (!empty($user['two_factor_enabled'])) {
            $this->session->set('pending_2fa_user_id', $user['id']);
            return true;
        }

        // Regenerate session ID to prevent session fixation
        $this->session->regenerate();
        $this->session->set('user_id', $user['id']);
        $this->session->set('logged_in_at', time());

        return true;
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

        // Verify TOTP code (simplified - in production use a TOTP library)
        $expectedCode = $this->generateTOTP($user['two_factor_secret']);
        if ($code !== $expectedCode) {
            return false;
        }

        // Complete the login
        $this->session->remove('pending_2fa_user_id');
        $this->session->regenerate();
        $this->session->set('user_id', $user['id']);
        $this->session->set('logged_in_at', time());

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
     * Generate TOTP code (simplified implementation)
     */
    private function generateTOTP(string $secret): string
    {
        $time = floor(time() / 30);
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
}
