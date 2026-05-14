<?php
/**
 * Auth Controller
 * Handles user authentication, registration, and 2FA
 */

class AuthController extends BaseController
{
    private AuthService $authService;
    private AuditService $auditService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->auditService = new AuditService();
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

            $this->auditService->log($userId, 'user_registered', ['email' => $email]);
            $this->setFlash('success', 'Account created successfully. Please log in.');
            $this->redirect('/login');
        } catch (Exception $e) {
            $this->setFlash('error', $e->getMessage());
            $this->redirect('/register');
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
