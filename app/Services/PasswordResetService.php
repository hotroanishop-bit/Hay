<?php
/**
 * Password Reset Service
 * Handles password reset flow with email sending
 */

class PasswordResetService
{
    private PasswordReset $resetModel;
    private MailService $mailService;
    private User $userModel;

    public function __construct()
    {
        $this->resetModel = new PasswordReset();
        $this->mailService = new MailService();
        $this->userModel = new User();
    }

    /**
     * Send password reset email
     */
    public function sendResetEmail(string $email): bool
    {
        // Find user by email
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            // Return true to prevent email enumeration
            return true;
        }
        
        // Create reset token
        $plainToken = $this->resetModel->createToken($email);
        
        // Send email with reset link
        return $this->mailService->sendPasswordReset($user, $plainToken);
    }

    /**
     * Validate a reset token
     */
    public function validateToken(string $email, string $token): bool
    {
        return $this->resetModel->isValidToken($email, $token);
    }

    /**
     * Reset user password
     */
    public function resetPassword(string $email, string $token, string $newPassword): bool
    {
        // Validate token first
        if (!$this->validateToken($email, $token)) {
            return false;
        }
        
        // Find the user
        $user = $this->userModel->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Update password
        $hashedPassword = $this->userModel->hashPassword($newPassword);
        $updated = $this->userModel->update($user['id'], [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        if ($updated) {
            // Delete the used token
            $this->resetModel->deleteByEmail($email);
        }
        
        return $updated;
    }
}
