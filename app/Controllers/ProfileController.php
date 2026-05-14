<?php
/**
 * Profile Controller
 * Handles user profile settings, password changes, avatar uploads, and 2FA management
 */

class ProfileController extends BaseController
{
    private AuthService $authService;
    private User $userModel;
    private AuditService $auditService;
    private MailService $mailService;
    private EmailVerificationService $emailVerificationService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        $this->auditService = new AuditService();
        $this->mailService = new MailService();
        $this->emailVerificationService = new EmailVerificationService();
    }

    /**
     * Show profile settings page
     */
    public function index(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = 'profile';
        $this->render('profile/index', [
            'pageTitle' => 'Profile Settings',
            'currentPage' => $this->currentPage,
            'user' => $user
        ], ['profile'], ['profile']);
    }

    /**
     * Update profile information (name, email)
     */
    public function update(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Validate input
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }

        // Check if email is taken by another user
        if ($email !== $user['email']) {
            $existingUser = $this->userModel->findByEmail($email);
            if ($existingUser && $existingUser['id'] !== $user['id']) {
                $errors[] = 'This email is already registered';
            }
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('. ', $errors));
            $this->redirect('/profile');
            return;
        }

        // Update user
        $updateData = [
            'name' => $name,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // If email changed, clear verification
        if ($email !== $user['email']) {
            $updateData['email'] = $email;
            $updateData['email_verified_at'] = null;
        }

        $this->userModel->update($user['id'], $updateData);

        // Log the action
        $this->auditService->log($user['id'], 'profile_updated', [
            'name' => $name,
            'email_changed' => $email !== $user['email']
        ]);

        $this->setFlash('success', 'Profile updated successfully');
        $this->redirect('/profile');
    }

    /**
     * Show password change form
     */
    public function showPassword(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = 'profile';
        $this->render('profile/password', [
            'pageTitle' => 'Change Password',
            'currentPage' => $this->currentPage,
            'user' => $user
        ], ['profile'], ['profile']);
    }

    /**
     * Update password
     */
    public function updatePassword(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        // Validate current password
        if (!$this->userModel->verifyPassword($currentPassword, $user['password_hash'])) {
            $this->setFlash('error', 'Current password is incorrect');
            $this->redirect('/profile/password');
            return;
        }

        // Validate new password
        if (strlen($newPassword) < 8) {
            $this->setFlash('error', 'New password must be at least 8 characters');
            $this->redirect('/profile/password');
            return;
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'New passwords do not match');
            $this->redirect('/profile/password');
            return;
        }

        // Update password
        $hashedPassword = $this->userModel->hashPassword($newPassword);
        $this->userModel->update($user['id'], [
            'password_hash' => $hashedPassword,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log the action
        $this->auditService->log($user['id'], 'password_changed', []);

        $this->setFlash('success', 'Password changed successfully');
        $this->redirect('/profile');
    }

    /**
     * Upload avatar image
     */
    public function uploadAvatar(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Check if file was uploaded
        if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'No file uploaded or upload error occurred');
            $this->redirect('/profile');
            return;
        }

        $file = $_FILES['avatar'];

        // Validate file type
        $allowedTypes = ['image/png', 'image/jpeg', 'image/jpg', 'image/gif'];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mimeType, $allowedTypes)) {
            $this->setFlash('error', 'Invalid file type. Only PNG, JPG, JPEG, and GIF are allowed');
            $this->redirect('/profile');
            return;
        }

        // Validate file size (max 2MB)
        $maxSize = 2 * 1024 * 1024; // 2MB in bytes
        if ($file['size'] > $maxSize) {
            $this->setFlash('error', 'File size must not exceed 2MB');
            $this->redirect('/profile');
            return;
        }

        // Create uploads directory if it doesn't exist
        $uploadDir = PUBLIC_PATH . '/uploads/avatars';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = $user['id'] . '_' . time() . '.' . $extension;
        $filepath = $uploadDir . '/' . $filename;

        // Delete old avatar if exists
        if (!empty($user['avatar_url'])) {
            $oldFilename = basename($user['avatar_url']);
            $oldFilepath = $uploadDir . '/' . $oldFilename;
            if (file_exists($oldFilepath)) {
                unlink($oldFilepath);
            }
        }

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->setFlash('error', 'Failed to save file');
            $this->redirect('/profile');
            return;
        }

        // Update user avatar URL
        $avatarUrl = '/uploads/avatars/' . $filename;
        $this->userModel->updateAvatar($user['id'], $avatarUrl);

        // Log the action
        $this->auditService->log($user['id'], 'avatar_uploaded', ['filename' => $filename]);

        $this->setFlash('success', 'Avatar uploaded successfully');
        $this->redirect('/profile');
    }

    /**
     * Show 2FA settings page
     */
    public function show2FA(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Generate new secret if 2FA is not enabled
        $secret = null;
        $qrCodeUrl = null;
        
        if (empty($user['two_factor_enabled'])) {
            $secret = $this->generateTOTPSecret();
            $_SESSION['pending_2fa_secret'] = $secret;
            
            // Generate QR code URL for authenticator apps
            $config = require CONFIG_PATH . '/app.php';
            $appName = urlencode($config['name'] ?? 'App');
            $userEmail = urlencode($user['email']);
            $qrCodeUrl = "otpauth://totp/{$appName}:{$userEmail}?secret={$secret}&issuer={$appName}";
        }

        $this->currentPage = 'profile';
        $this->render('profile/2fa', [
            'pageTitle' => 'Two-Factor Authentication',
            'currentPage' => $this->currentPage,
            'user' => $user,
            'secret' => $secret,
            'qrCodeUrl' => $qrCodeUrl
        ], ['profile'], ['profile']);
    }

    /**
     * Enable 2FA
     */
    public function enable2FA(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $code = trim($_POST['code'] ?? '');
        $secret = $_SESSION['pending_2fa_secret'] ?? null;

        if (empty($code) || empty($secret)) {
            $this->setFlash('error', 'Invalid request. Please try again');
            $this->redirect('/profile/2fa');
            return;
        }

        // Verify the code with drift tolerance
        if (!$this->verifyTOTP($secret, $code)) {
            $this->setFlash('error', 'Invalid verification code');
            $this->redirect('/profile/2fa');
            return;
        }

        // Enable 2FA
        $this->userModel->enable2FA($user['id'], base64_encode($secret));
        unset($_SESSION['pending_2fa_secret']);

        // Log the action
        $this->auditService->log($user['id'], '2fa_enabled', []);

        $this->setFlash('success', 'Two-factor authentication has been enabled');
        $this->redirect('/profile/2fa');
    }

    /**
     * Disable 2FA
     */
    public function disable2FA(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $code = trim($_POST['code'] ?? '');

        if (empty($code)) {
            $this->setFlash('error', 'Please enter your verification code');
            $this->redirect('/profile/2fa');
            return;
        }

        // Verify the code
        $secret = $user['two_factor_secret'];
        if (empty($secret)) {
            $this->setFlash('error', '2FA is not enabled');
            $this->redirect('/profile/2fa');
            return;
        }

        // Verify the code with drift tolerance
        if (!$this->verifyTOTP(base64_decode($secret), $code)) {
            $this->setFlash('error', 'Invalid verification code');
            $this->redirect('/profile/2fa');
            return;
        }

        // Disable 2FA
        $this->userModel->disable2FA($user['id']);

        // Log the action
        $this->auditService->log($user['id'], '2fa_disabled', []);

        $this->setFlash('success', 'Two-factor authentication has been disabled');
        $this->redirect('/profile/2fa');
    }

    /**
     * Delete account
     */
    public function deleteAccount(): void
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        $password = $_POST['password'] ?? '';

        // Verify password
        if (!$this->userModel->verifyPassword($password, $user['password_hash'])) {
            $this->setFlash('error', 'Incorrect password');
            $this->redirect('/profile');
            return;
        }

        // Anonymize user data instead of hard delete
        $anonymizedEmail = 'deleted_' . $user['id'] . '_' . time() . '@deleted.local';
        $this->userModel->update($user['id'], [
            'name' => 'Deleted User',
            'email' => $anonymizedEmail,
            'password_hash' => '',
            'avatar_url' => null,
            'two_factor_secret' => null,
            'two_factor_enabled' => 0,
            'is_banned' => 1,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log the action
        $this->auditService->log($user['id'], 'account_deleted', [
            'original_email' => $user['email']
        ]);

        // Logout
        $this->authService->logout();

        $this->setFlash('success', 'Your account has been deleted');
        $this->redirect('/login');
    }

    /**
     * Generate a random TOTP secret
     */
    private function generateTOTPSecret(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $secret = '';
        for ($i = 0; $i < 16; $i++) {
            $secret .= $chars[random_int(0, 31)];
        }
        return $secret;
    }

    /**
     * Generate TOTP code from secret for a specific time window
     */
    private function generateTOTPForTime(string $secret, int $time): string
    {
        $binaryTime = pack('N*', 0) . pack('N*', $time);
        
        // Decode base32 secret
        $secretBytes = $this->base32Decode($secret);
        
        $hash = hash_hmac('sha1', $binaryTime, $secretBytes, true);
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
     * Generate TOTP code from secret (current window)
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

    /**
     * Decode base32 string
     */
    private function base32Decode(string $input): string
    {
        $map = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
        $input = strtoupper($input);
        $input = str_replace('=', '', $input);
        
        $buffer = 0;
        $bitsLeft = 0;
        $result = '';
        
        for ($i = 0; $i < strlen($input); $i++) {
            $val = strpos($map, $input[$i]);
            if ($val === false) {
                continue;
            }
            
            $buffer = ($buffer << 5) | $val;
            $bitsLeft += 5;
            
            if ($bitsLeft >= 8) {
                $bitsLeft -= 8;
                $result .= chr(($buffer >> $bitsLeft) & 0xFF);
            }
        }
        
        return $result;
    }
}
