<?php
/**
 * Auto Top-Up Controller
 * Handles auto top-up settings management
 */

class AutoTopupController extends BaseController
{
    private AuthService $authService;
    private AutoTopupSetting $settingModel;
    private AutoTopupService $autoTopupService;

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->settingModel = new AutoTopupSetting();
        $this->autoTopupService = new AutoTopupService();
    }

    /**
     * Show auto top-up settings page
     */
    public function index(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Get or create settings
        $settings = $this->autoTopupService->getOrCreateSettings($user['id']);
        
        // Calculate cooldown info
        $isOnCooldown = $this->autoTopupService->isOnCooldown($settings);
        $cooldownRemaining = $this->autoTopupService->getCooldownRemaining($settings);

        $this->currentPage = 'billing-auto-topup';
        $this->render('billing/auto_topup', [
            'pageTitle' => 'Auto Top-Up Settings',
            'currentPage' => $this->currentPage,
            'user' => $user,
            'settings' => $settings,
            'isOnCooldown' => $isOnCooldown,
            'cooldownRemaining' => $cooldownRemaining,
            'flash' => $_SESSION['flash'] ?? null
        ], ['auto_topup'], []);

        unset($_SESSION['flash']);
    }

    /**
     * Update auto top-up settings
     */
    public function update(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/billing/auto-topup');
            return;
        }

        // Validate and sanitize inputs
        $threshold = (float) ($_POST['threshold'] ?? 10);
        $amount = (float) ($_POST['amount'] ?? 50);
        $cooldownHours = (int) ($_POST['cooldown_hours'] ?? 24);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        // Validation
        $errors = [];

        if ($threshold < 1 || $threshold > 1000) {
            $errors[] = 'Threshold must be between $1 and $1,000';
        }

        if ($amount < 5 || $amount > 5000) {
            $errors[] = 'Top-up amount must be between $5 and $5,000';
        }

        if ($cooldownHours < 1 || $cooldownHours > 168) {
            $errors[] = 'Cooldown must be between 1 and 168 hours (1 week)';
        }

        if ($amount <= $threshold) {
            $errors[] = 'Top-up amount must be greater than the threshold';
        }

        if (!empty($errors)) {
            $this->setFlash('error', implode('. ', $errors));
            $this->redirect('/billing/auto-topup');
            return;
        }

        // Update settings
        $this->settingModel->upsert($user['id'], [
            'threshold' => $threshold,
            'amount' => $amount,
            'cooldown_hours' => $cooldownHours,
            'is_active' => $isActive
        ]);

        $this->setFlash('success', 'Auto top-up settings updated successfully.');
        $this->redirect('/billing/auto-topup');
    }

    /**
     * Toggle auto top-up on/off
     */
    public function toggle(): void
    {
        $user = $this->authService->user();
        if (!$user) {
            $this->redirect('/login');
            return;
        }

        // Verify CSRF token
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
            $this->setFlash('error', 'Invalid request. Please try again.');
            $this->redirect('/billing/auto-topup');
            return;
        }

        // Get current settings
        $settings = $this->settingModel->getByUser($user['id']);
        
        if (!$settings) {
            // Create default settings if not exists
            $this->settingModel->upsert($user['id'], [
                'threshold' => 10.00,
                'amount' => 50.00,
                'cooldown_hours' => 24,
                'is_active' => 1
            ]);
            $this->setFlash('success', 'Auto top-up enabled with default settings.');
        } else {
            // Toggle existing
            $this->settingModel->toggleActive($user['id']);
            $newStatus = $settings['is_active'] ? 'disabled' : 'enabled';
            $this->setFlash('success', "Auto top-up {$newStatus}.");
        }

        $this->redirect('/billing/auto-topup');
    }
}
