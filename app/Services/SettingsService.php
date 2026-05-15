<?php
/**
 * Settings Service
 * Provides cached access to application settings
 * Supports both legacy Setting model and new SystemSetting model
 */

class SettingsService
{
    private static ?array $cache = null;
    private static bool $useSystemSettings = true;

    // Default values for settings (fallbacks if not in DB)
    private static array $defaults = [
        // App settings (new system_settings)
        'app_name' => 'Hay API Gateway',
        'app_url' => 'https://yourdomain.com',
        'app_logo' => '/assets/img/logo.png',
        'app_favicon' => '/assets/img/favicon.ico',
        'app_debug' => false,
        
        // Legacy mappings
        'site_name' => 'Hay API Platform',
        'site_url' => 'http://localhost',
        'logo_url' => '',
        'favicon_url' => '',
        
        // Maintenance settings
        'maintenance_mode' => false,
        'maintenance_message' => 'We are currently performing scheduled maintenance. Please check back soon.',
        
        // Payment settings (new)
        'vietqr_bank_id' => 'MB',
        'vietqr_account_no' => '',
        'vietqr_account_name' => '',
        'payment_min_deposit' => 10000,
        'payment_max_deposit' => 50000000,
        
        // Legacy payment mappings
        'bank_name' => '',
        'bank_account_number' => '',
        'account_holder_name' => '',
        
        // Mail settings (new)
        'mail_host' => 'smtp.gmail.com',
        'mail_port' => 587,
        'mail_username' => '',
        'mail_password' => '',
        'mail_encryption' => 'tls',
        'mail_from_name' => 'Hay API Gateway',
        'mail_from_address' => '',
        
        // Legacy SMTP mappings
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls',
        
        // Telegram settings
        'telegram_bot_token' => '',
        'telegram_admin_chat_id' => '',
        'telegram_enabled' => false,
        
        // API Gateway settings
        'api_upstream_url' => 'https://api.openai.com',
        'api_upstream_key' => '',
        'api_proxy_host' => '',
        'api_proxy_port' => '',
        'api_proxy_user' => '',
        'api_proxy_pass' => '',
        'api_retry_count' => 1,
        'api_timeout' => 60,
        
        // Limits
        'rate_limit_default' => 60,
        'rate_limit_daily' => 1000,
        'max_api_keys' => 10,
        'session_timeout' => 120,
        
        // Legacy limit settings
        'default_plan_id' => 0,
        'min_deposit' => 10000,
        'max_deposit' => 50000000,
        
        // Registration
        'registration_enabled' => true,
        'default_user_balance' => 0,
        'welcome_bonus' => 0,
        
        // Rewards
        'checkin_base_reward' => 10,
        'checkin_streak_bonus' => 10,
        'checkin_max_multiplier' => 2,
        
        // Referral
        'referral_enabled' => true,
        'referral_commission' => 10,
        'referral_bonus' => 0,
    ];

    /**
     * Get a setting value with caching (static method)
     */
    public static function get(string $key, $default = null)
    {
        if (self::$cache === null) {
            self::loadAll();
        }
        
        // Check cache first
        if (isset(self::$cache[$key])) {
            return self::$cache[$key];
        }
        
        // Use default from defaults array if not provided
        if ($default === null && array_key_exists($key, self::$defaults)) {
            return self::$defaults[$key];
        }
        
        return $default;
    }

    /**
     * Load all settings into cache
     */
    public static function loadAll(): void
    {
        try {
            // Try new system_settings table first
            $model = new SystemSetting();
            self::$cache = $model->getAll();
        } catch (Exception $e) {
            // Fall back to legacy settings table
            try {
                $model = new Setting();
                self::$cache = $model->getAll();
            } catch (Exception $e2) {
                self::$cache = [];
            }
        }
    }

    /**
     * Clear cache (call after update)
     */
    public static function clearCache(): void
    {
        self::$cache = null;
    }

    /**
     * Update a setting value (static method)
     */
    public static function set(string $key, $value, ?int $adminId = null): bool
    {
        try {
            $model = new SystemSetting();
            $result = $model->set($key, $value, $adminId);
            self::clearCache();
            return $result;
        } catch (Exception $e) {
            // Fall back to legacy
            try {
                $model = new Setting();
                $result = $model->set($key, $value);
                self::clearCache();
                return $result;
            } catch (Exception $e2) {
                return false;
            }
        }
    }

    /**
     * Get all settings as array (includes defaults)
     */
    public static function getAll(): array
    {
        if (self::$cache === null) {
            self::loadAll();
        }
        return array_merge(self::$defaults, self::$cache);
    }

    /**
     * Get settings by group (uses SystemSetting model)
     */
    public static function getGroup(string $group): array
    {
        try {
            $model = new SystemSetting();
            return $model->getGroup($group);
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get all settings grouped
     */
    public static function getAllGrouped(): array
    {
        try {
            $model = new SystemSetting();
            return $model->getAllGrouped();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Get available groups
     */
    public static function getGroups(): array
    {
        try {
            $model = new SystemSetting();
            return $model->getGroups();
        } catch (Exception $e) {
            return ['general', 'app', 'mail', 'payment', 'api', 'limits', 'maintenance'];
        }
    }

    // =====================
    // Helper methods
    // =====================

    /**
     * Get app name
     */
    public static function appName(): string
    {
        return self::get('app_name', self::get('site_name', 'Hay API Gateway'));
    }

    /**
     * Get app URL
     */
    public static function appUrl(): string
    {
        return self::get('app_url', self::get('site_url', ''));
    }

    /**
     * Check if maintenance mode is enabled
     */
    public static function isMaintenanceMode(): bool
    {
        return (bool) self::get('maintenance_mode', false);
    }

    /**
     * Get maintenance message
     */
    public static function maintenanceMessage(): string
    {
        return self::get('maintenance_message', '');
    }

    /**
     * Check if registration is enabled
     */
    public static function isRegistrationEnabled(): bool
    {
        return (bool) self::get('registration_enabled', true);
    }

    /**
     * Check if Telegram is enabled
     */
    public static function isTelegramEnabled(): bool
    {
        return (bool) self::get('telegram_enabled', false);
    }

    /**
     * Check if referral is enabled
     */
    public static function isReferralEnabled(): bool
    {
        return (bool) self::get('referral_enabled', true);
    }

    /**
     * Check if debug mode is enabled
     */
    public static function isDebugMode(): bool
    {
        return (bool) self::get('app_debug', false);
    }

    // =====================
    // Instance methods for backward compatibility
    // =====================

    /**
     * Get setting (instance method for backward compatibility)
     */
    public function getSetting(string $key, $default = null)
    {
        return self::get($key, $default);
    }

    /**
     * Set setting (instance method for backward compatibility)
     */
    public function setSetting(string $key, $value, string $type = 'string'): bool
    {
        return self::set($key, $value);
    }

    /**
     * Refresh cache (instance method)
     */
    public function refreshCache(): void
    {
        self::clearCache();
        self::loadAll();
    }

    /**
     * Get bank list (delegate to VietQRService)
     */
    public function getBankList(): array
    {
        $vietQRService = new VietQRService();
        return $vietQRService->getBankList();
    }

    /**
     * Get general settings as a group (backward compat)
     */
    public function getGeneralSettings(): array
    {
        return [
            'site_name' => self::get('site_name', self::get('app_name')),
            'site_url' => self::get('site_url', self::get('app_url')),
            'logo_url' => self::get('logo_url', self::get('app_logo')),
            'favicon_url' => self::get('favicon_url', self::get('app_favicon')),
        ];
    }

    /**
     * Get maintenance settings as a group (backward compat)
     */
    public function getMaintenanceSettings(): array
    {
        return [
            'maintenance_mode' => self::get('maintenance_mode'),
            'maintenance_message' => self::get('maintenance_message'),
        ];
    }

    /**
     * Get SMTP settings as a group (backward compat)
     */
    public function getSmtpSettings(): array
    {
        return [
            'host' => self::get('smtp_host', self::get('mail_host')),
            'port' => self::get('smtp_port', self::get('mail_port')),
            'username' => self::get('smtp_username', self::get('mail_username')),
            'password' => self::get('smtp_password', self::get('mail_password')),
            'encryption' => self::get('smtp_encryption', self::get('mail_encryption')),
        ];
    }

    /**
     * Get payment settings as a group (backward compat)
     */
    public function getPaymentSettings(): array
    {
        return [
            'bank_name' => self::get('bank_name', self::get('vietqr_bank_id')),
            'bank_account_number' => self::get('bank_account_number', self::get('vietqr_account_no')),
            'account_holder_name' => self::get('account_holder_name', self::get('vietqr_account_name')),
        ];
    }

    /**
     * Get limit settings as a group (backward compat)
     */
    public function getLimitSettings(): array
    {
        return [
            'default_plan_id' => self::get('default_plan_id'),
            'min_deposit' => self::get('min_deposit', self::get('payment_min_deposit')),
            'max_deposit' => self::get('max_deposit', self::get('payment_max_deposit')),
        ];
    }
}
