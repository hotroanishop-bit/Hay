<?php
/**
 * Auth Controller
 * Handles user authentication, registration, 2FA, password reset, and email verification
 */

class AuthController extends BaseController
{
    private AuthService $authService;
    private AuditService $auditService;
    private User $userModel;
    private PasswordResetService $passwordResetService;
    private EmailVerificationService $emailVerificationService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $this->userModel = new User();
        $this->authService = new AuthService($sessionService, $this->userModel);
        $this->auditService = new AuditService();
        $this->passwordResetService = new PasswordResetService();
        $this->emailVerificationService = new EmailVerificationService();
    }

    /**
     * Show login page
     */
    public function showLogin(): void
    {
        // Redirect if already logged in
        if ($this->authService->check()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->currentPage = 'login';
        $this->render('auth/login', [
            'pageTitle' => 'Login',
            'currentPage' => $this->currentPage
        ], ['auth'], ['auth']);
    }

    /**
     * Process login form
     */
    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        // Basic validation
        if (empty($email) || empty($password)) {
            $this->setFlash('error', 'Please enter both email and password');
            $this->redirect('/login');
            return;
        }

        // Attempt login
        $result = $this->authService->attempt($email, $password);

        if (!$result) {
            $this->setFlash('error', 'Invalid email or password');
            $this->redirect('/login');
            return;
        }

        // Check if 2FA is required
        if ($this->authService->isPending2FA()) {
            $this->redirect('/2fa');
            return;
        }

        // Log successful login
        $user = $this->authService->user();
        if ($user) {
            $this->auditService->logLogin($user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown', true);
        }

        $this->setFlash('success', 'Welcome back!');
        $this->redirect('/dashboard');
    }

    /**
     * Show registration page
     */
    public function showRegister(): void
    {
        // Redirect if already logged in
        if ($this->authService->check()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->currentPage = 'register';
        $this->render('auth/register', [
            'pageTitle' => 'Create Account',
            'currentPage' => $this->currentPage
        ], ['auth'], ['auth']);
    }

    /**
     * Process registration form
     */
    public function register(): void
    {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Basic validation
        $errors = [];
        
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if ($password !== $passwordConfirm) {
            $errors[] = 'Passwords do not match';
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('. ', $errors));
            $this->redirect('/register');
            return;
        }

        try {
            $userId = $this->authService->register([
                'name' => $name,
                'email' => $email,
                'password' => $password
            ]);

            // Get the newly created user
            $user = $this->userModel->find($userId);

            // Send verification email
            if ($user) {
                $this->emailVerificationService->sendVerificationEmail($user);
            }

            $this->auditService->log($userId, 'user_registered', ['email' => $email]);
            $this->setFlash('success', 'Account created successfully. Please check your email to verify your account.');
            $this->redirect('/verify-email');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/register');
        }
    }

    /**
     * Show forgot password page
     */
    public function showForgotPassword(): void
    {
        // Redirect if already logged in
        if ($this->authService->check()) {
            $this->redirect('/dashboard');
            return;
        }

        $this->currentPage = 'forgot-password';
        $this->render('auth/forgot_password', [
            'pageTitle' => 'Forgot Password',
            'currentPage' => $this->currentPage
        ], ['auth'], ['auth']);
    }

    /**
     * Process forgot password form
     */
    public function forgotPassword(): void
    {
        $email = trim($_POST['email'] ?? '');

        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('error', 'Please enter a valid email address');
            $this->redirect('/forgot-password');
            return;
        }

        // Send reset email (returns true even if user not found to prevent enumeration)
        $this->passwordResetService->sendResetEmail($email);

        $this->setFlash('success', 'If an account with that email exists, you will receive a password reset link shortly.');
        $this->redirect('/forgot-password');
    }

    /**
     * Show reset password page
     */
    public function showResetPassword(): void
    {
        // Redirect if already logged in
        if ($this->authService->check()) {
            $this->redirect('/dashboard');
            return;
        }

        $token = $_GET['token'] ?? '';
        $email = $_GET['email'] ?? '';

        if (empty($token) || empty($email)) {
            $this->setFlash('error', 'Invalid password reset link');
            $this->redirect('/forgot-password');
            return;
        }

        $this->currentPage = 'reset-password';
        $this->render('auth/reset_password', [
            'pageTitle' => 'Reset Password',
            'currentPage' => $this->currentPage,
            'token' => $token,
            'email' => $email
        ], ['auth'], ['auth']);
    }

    /**
     * Process password reset form
     */
    public function resetPassword(): void
    {
        $token = $_POST['token'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $passwordConfirm = $_POST['password_confirm'] ?? '';

        // Validate
        if (empty($token) || empty($email)) {
            $this->setFlash('error', 'Invalid password reset request');
            $this->redirect('/forgot-password');
            return;
        }

        if (strlen($password) < 8) {
            $this->setFlash('error', 'Password must be at least 8 characters');
            $this->redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            return;
        }

        if ($password !== $passwordConfirm) {
            $this->setFlash('error', 'Passwords do not match');
            $this->redirect('/reset-password?token=' . urlencode($token) . '&email=' . urlencode($email));
            return;
        }

        // Attempt password reset
        if ($this->passwordResetService->resetPassword($email, $token, $password)) {
            $user = $this->userModel->findByEmail($email);
            if ($user) {
                $this->auditService->log($user['id'], 'password_reset_completed', []);
            }
            
            $this->setFlash('success', 'Your password has been reset. You can now log in.');
            $this->redirect('/login');
        } else {
            $this->setFlash('error', 'Invalid or expired password reset link');
            $this->redirect('/forgot-password');
        }
    }

    /**
     * Show email verification waiting page
     */
    public function showVerifyEmail(): void
    {
        $this->currentPage = 'verify-email';
        $this->render('auth/verify_email', [
            'pageTitle' => 'Verify Your Email',
            'currentPage' => $this->currentPage,
            'user' => $this->authService->user()
        ], ['auth'], ['auth']);
    }

    /**
     * Process email verification
     */
    public function verifyEmail(): void
    {
        $token = $_GET['token'] ?? '';

        if (empty($token)) {
            $this->setFlash('error', 'Invalid verification link');
            $this->redirect('/login');
            return;
        }

        $userId = $this->emailVerificationService->verify($token);

        if ($userId !== null) {
            $this->auditService->log($userId, 'email_verified', []);
            $this->setFlash('success', 'Your email has been verified. You can now log in.');
            $this->redirect('/login');
        } else {
            $this->setFlash('error', 'Invalid or expired verification link');
            $this->redirect('/login');
        }
    }

    /**
     * Resend verification email
     */
    public function resendVerification(): void
    {
        $user = $this->authService->user();

        // Also allow resending by email for non-logged-in users
        $email = $_POST['email'] ?? '';

        if ($user) {
            if ($user['email_verified_at'] !== null) {
                $this->setFlash('info', 'Your email is already verified');
                $this->redirect('/dashboard');
                return;
            }
            
            $this->emailVerificationService->resendVerification($user['id']);
            $this->setFlash('success', 'Verification email has been resent');
            $this->redirect('/verify-email');
        } elseif (!empty($email)) {
            $targetUser = $this->userModel->findByEmail($email);
            
            if ($targetUser && $targetUser['email_verified_at'] === null) {
                $this->emailVerificationService->resendVerification($targetUser['id']);
            }
            
            // Always show success to prevent enumeration
            $this->setFlash('success', 'If an account with that email exists and is not verified, a verification email has been sent.');
            $this->redirect('/verify-email');
        } else {
            $this->setFlash('error', 'Please provide your email address');
            $this->redirect('/verify-email');
        }
    }

    /**
     * Show 2FA verification page
     */
    public function show2FA(): void
    {
        // Redirect if not pending 2FA
        if (!$this->authService->isPending2FA()) {
            $this->redirect('/login');
            return;
        }

        $this->currentPage = '2fa';
        $this->render('auth/2fa', [
            'pageTitle' => 'Two-Factor Authentication',
            'currentPage' => $this->currentPage
        ], ['auth'], ['auth']);
    }

    /**
     * Verify 2FA code
     */
    public function verify2FA(): void
    {
        $code = trim($_POST['code'] ?? '');

        if (empty($code)) {
            $this->setFlash('error', 'Please enter the verification code');
            $this->redirect('/2fa');
            return;
        }

        if ($this->authService->verify2FA($code)) {
            $user = $this->authService->user();
            if ($user) {
                $this->auditService->logLogin($user['id'], $_SERVER['REMOTE_ADDR'] ?? 'unknown', true);
            }
            
            $this->setFlash('success', 'Welcome back!');
            $this->redirect('/dashboard');
        } else {
            $this->setFlash('error', 'Invalid verification code');
            $this->redirect('/2fa');
        }
    }

    /**
     * Log out the current user
     */
    public function logout(): void
    {
        $user = $this->authService->user();
        
        if ($user) {
            $this->auditService->log($user['id'], 'user_logout', []);
        }
        
        $this->authService->logout();
        $this->setFlash('success', 'You have been logged out');
        $this->redirect('/login');
    }
}
