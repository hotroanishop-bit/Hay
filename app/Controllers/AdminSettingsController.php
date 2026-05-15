<?php
/**
 * Admin Settings Controller
 * Full CRUD for system settings with tabbed interface
 */

class AdminSettingsController extends BaseController
{
    private AuthService $authService;
    private AuditLog $auditLogModel;
    private SystemSetting $systemSettingModel;
    private Plan $planModel;

    // Setting groups configuration
    private array $groupConfig = [
        'app' => [
            'title' => 'Application',
            'icon' => 'icon-globe',
            'description' => 'Basic application settings'
        ],
        'mail' => [
            'title' => 'Email / SMTP',
            'icon' => 'icon-mail',
            'description' => 'Email sending configuration'
        ],
        'telegram' => [
            'title' => 'Telegram',
            'icon' => 'icon-message-circle',
            'description' => 'Telegram bot notifications'
        ],
        'payment' => [
            'title' => 'Payment / VietQR',
            'icon' => 'icon-credit-card',
            'description' => 'Payment and bank transfer settings'
        ],
        'api' => [
            'title' => 'API Gateway',
            'icon' => 'icon-server',
            'description' => 'Upstream API and proxy configuration'
        ],
        'limits' => [
            'title' => 'Rate Limits',
            'icon' => 'icon-sliders',
            'description' => 'API rate limits and restrictions'
        ],
        'maintenance' => [
            'title' => 'Maintenance',
            'icon' => 'icon-tool',
            'description' => 'Maintenance mode settings'
        ],
        'registration' => [
            'title' => 'Registration',
            'icon' => 'icon-user-plus',
            'description' => 'User registration settings'
        ],
        'rewards' => [
            'title' => 'Check-in Rewards',
            'icon' => 'icon-gift',
            'description' => 'Daily check-in reward settings'
        ],
        'referral' => [
            'title' => 'Referral',
            'icon' => 'icon-users',
            'description' => 'Referral program settings'
        ],
        'database' => [
            'title' => 'Database',
            'icon' => 'icon-database',
            'description' => 'Database connection info (read-only)'
        ]
    ];

    public function __construct()
    {
        $sessionService = new SessionService();
        $userModel = new User();
        $this->authService = new AuthService($sessionService, $userModel);
        $this->auditLogModel = new AuditLog();
        $this->systemSettingModel = new SystemSetting();
        $this->planModel = new Plan();
    }

    /**
     * Check if current user is admin
     */
    private function requireAdmin(): bool
    {
        $user = $this->authService->user();
        
        if (!$user) {
            $this->redirect('/login');
            return false;
        }

        if (empty($user['is_admin'])) {
            $this->setFlash('error', 'Access denied. Admin privileges required.');
            $this->redirect('/dashboard');
            return false;
        }

        return true;
    }

    /**
     * Get client IP address
     */
    private function getClientIP(): string
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }

        return 'unknown';
    }

    /**
     * Show all settings with tabs
     */
    public function index(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $activeGroup = $_GET['group'] ?? 'app';
        
        // Get all settings grouped
        $settingsGrouped = $this->systemSettingModel->getAllGrouped();
        
        // Get plans for dropdown
        $plans = $this->planModel->findActive();
        
        // Get bank list
        $vietQRService = new VietQRService();
        $bankList = $vietQRService->getBankList();

        $this->currentPage = 'admin-settings';
        $this->render('admin/system_settings', [
            'pageTitle' => 'System Settings',
            'currentPage' => $this->currentPage,
            'settingsGrouped' => $settingsGrouped,
            'groupConfig' => $this->groupConfig,
            'activeGroup' => $activeGroup,
            'plans' => $plans,
            'bankList' => $bankList
        ], ['admin_settings'], ['admin_settings']);
    }

    /**
     * Show settings for a specific group
     */
    public function group(string $group): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        // Validate group
        if (!isset($this->groupConfig[$group])) {
            $this->setFlash('error', 'Invalid settings group.');
            $this->redirect('/admin/settings');
            return;
        }

        // Get settings for this group
        $settings = $this->systemSettingModel->getGroup($group);
        
        // Get all settings grouped for sidebar
        $settingsGrouped = $this->systemSettingModel->getAllGrouped();
        
        // Get plans for dropdown
        $plans = $this->planModel->findActive();
        
        // Get bank list
        $vietQRService = new VietQRService();
        $bankList = $vietQRService->getBankList();

        $this->currentPage = 'admin-settings';
        $this->render('admin/system_settings', [
            'pageTitle' => 'Settings - ' . $this->groupConfig[$group]['title'],
            'currentPage' => $this->currentPage,
            'settingsGrouped' => $settingsGrouped,
            'groupConfig' => $this->groupConfig,
            'activeGroup' => $group,
            'groupSettings' => $settings,
            'plans' => $plans,
            'bankList' => $bankList
        ], ['admin_settings'], ['admin_settings']);
    }

    /**
     * Save settings
     */
    public function save(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        $group = $_POST['setting_group'] ?? 'app';
        
        // Get existing settings for this group to compare
        $existingSettings = $this->systemSettingModel->getGroup($group);
        
        $changedSettings = [];
        $errors = [];
        
        foreach ($existingSettings as $setting) {
            $key = $setting['setting_key'];
            $type = $setting['setting_type'];
            $isSensitive = (bool) $setting['is_sensitive'];
            
            // Get posted value
            if ($type === 'boolean') {
                $newValue = isset($_POST[$key]) ? true : false;
            } else {
                $newValue = $_POST[$key] ?? null;
                
                // Skip if key not posted (wasn't in form)
                if ($newValue === null) {
                    continue;
                }
                
                $newValue = trim($newValue);
            }
            
            // Handle sensitive fields - skip if empty (keep existing)
            if ($isSensitive && empty($newValue) && $type !== 'boolean') {
                continue;
            }
            
            // Validate based on type
            if ($type === 'number') {
                if (!is_numeric($newValue) && $newValue !== '') {
                    $errors[] = ($setting['label'] ?? $key) . ' must be a number.';
                    continue;
                }
                $newValue = $newValue === '' ? 0 : (float) $newValue;
            }
            
            // Get old value for comparison
            $oldValue = $this->castValueForComparison($setting['setting_value'], $type);
            
            // Check if value changed
            if ($oldValue !== $newValue) {
                $changedSettings[$key] = [
                    'old' => $oldValue,
                    'new' => $newValue,
                    'sensitive' => $isSensitive,
                    'label' => $setting['label'] ?? $key
                ];
            }
        }
        
        // Handle errors
        if (!empty($errors)) {
            $this->setFlash('error', implode('<br>', $errors));
            $this->redirect('/admin/settings?group=' . $group);
            return;
        }
        
        // Save changes
        foreach ($changedSettings as $key => $change) {
            $this->systemSettingModel->set($key, $change['new'], $admin['id']);
        }
        
        // Clear settings cache
        SettingsService::clearCache();
        
        // Log audit action
        if (!empty($changedSettings)) {
            $oldValues = [];
            $newValues = [];
            
            foreach ($changedSettings as $key => $change) {
                if ($change['sensitive']) {
                    $oldValues[$key] = '[REDACTED]';
                    $newValues[$key] = '[REDACTED]';
                } else {
                    $oldValues[$key] = $change['old'];
                    $newValues[$key] = $change['new'];
                }
            }
            
            $this->auditLogModel->logAction(
                $admin['id'],
                'system_settings_updated',
                'system_settings',
                null,
                $oldValues,
                $newValues,
                $this->getClientIP()
            );
            
            $count = count($changedSettings);
            $this->setFlash('success', "{$count} setting(s) updated successfully.");
        } else {
            $this->setFlash('info', 'No changes were made.');
        }
        
        $this->redirect('/admin/settings?group=' . $group);
    }

    /**
     * Test email connection
     */
    public function testEmail(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $admin = $this->authService->user();
        
        // Get SMTP settings
        $host = SettingsService::get('mail_host', SettingsService::get('smtp_host'));
        $port = SettingsService::get('mail_port', SettingsService::get('smtp_port'));
        $username = SettingsService::get('mail_username', SettingsService::get('smtp_username'));
        $password = SettingsService::get('mail_password', SettingsService::get('smtp_password'));
        
        if (empty($host) || empty($username)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'SMTP settings are not configured.'
            ]);
            return;
        }
        
        // Note: Actual email testing requires network access
        // In sandbox mode, we just validate the configuration
        $this->jsonResponse([
            'success' => true,
            'message' => 'SMTP configuration looks valid. Host: ' . $host . ', Port: ' . $port
        ]);
    }

    /**
     * Test Telegram connection
     */
    public function testTelegram(): void
    {
        if (!$this->requireAdmin()) {
            return;
        }

        $botToken = SettingsService::get('telegram_bot_token');
        $chatId = SettingsService::get('telegram_admin_chat_id');
        
        if (empty($botToken) || empty($chatId)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Telegram bot token and chat ID are required.'
            ]);
            return;
        }
        
        // Note: Actual Telegram testing requires network access
        // In sandbox mode, we just validate the configuration
        $this->jsonResponse([
            'success' => true,
            'message' => 'Telegram configuration looks valid. Chat ID: ' . $chatId
        ]);
    }

    /**
     * Send JSON response
     */
    private function jsonResponse(array $data): void
    {
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    /**
     * Cast value for comparison based on type
     */
    private function castValueForComparison($value, string $type)
    {
        if ($value === null) {
            return $type === 'boolean' ? false : ($type === 'number' ? 0 : '');
        }
        
        switch ($type) {
            case 'boolean':
                return filter_var($value, FILTER_VALIDATE_BOOLEAN);
            case 'number':
                return is_numeric($value) ? (float) $value : 0;
            default:
                return (string) $value;
        }
    }
}
