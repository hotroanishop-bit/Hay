<?php
/**
 * Email Verification Service
 * Handles email verification flow for new users
 */

class EmailVerificationService
{
    private EmailVerification $verificationModel;
    private MailService $mailService;
    private User $userModel;

    public function __construct()
    {
        $this->verificationModel = new EmailVerification();
        $this->mailService = new MailService();
        $this->userModel = new User();
    }

    /**
     * Send verification email to user
     */
    public function sendVerificationEmail(array $user): bool
    {
        if (!isset($user['id']) || !isset($user['email'])) {
            return false;
        }
        
        // Create verification token
        $plainToken = $this->verificationModel->createToken($user['id']);
        
        // Build verification URL
        $config = require CONFIG_PATH . '/app.php';
        $verifyUrl = ($config['url'] ?? '') . '/verify-email?token=' . $plainToken;
        
        // Send email
        return $this->mailService->send(
            $user['email'],
            'Verify your email address',
            'verify-email',
            [
                'name' => $user['name'] ?? 'User',
                'verify_url' => $verifyUrl
            ]
        );
    }

    /**
     * Verify a token and mark user email as verified
     */
    public function verify(string $token): ?int
    {
        // Verify token and get user ID
        $userId = $this->verificationModel->verify($token);
        
        if ($userId === null) {
            return null;
        }
        
        // Mark user email as verified
        $this->userModel->verifyEmail($userId);
        
        return $userId;
    }

    /**
     * Resend verification email to user
     */
    public function resendVerification(int $userId): bool
    {
        // Get user data
        $user = $this->userModel->find($userId);
        
        if (!$user) {
            return false;
        }
        
        // Check if already verified
        if ($user['email_verified_at'] !== null) {
            return false;
        }
        
        // Delete old tokens and send new verification
        $this->verificationModel->deleteByUser($userId);
        
        return $this->sendVerificationEmail($user);
    }
}
