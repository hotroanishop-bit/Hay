<?php
/**
 * Settings Service
 * Provides cached access to application settings
 */

class SettingsService
{
    private Setting $settingModel;
    private static array $cache = [];
    private static bool $cacheLoaded = false;

    // Default values for settings (fallbacks if not in DB)
    private static array $defaults = [
        // General settings
        'site_name' => 'Hay API Platform',
        'site_url' => 'http://localhost',
        'logo_url' => '',
        'favicon_url' => '',
        
        // Maintenance settings
        'maintenance_mode' => false,
        'maintenance_message' => 'We are currently performing scheduled maintenance. Please check back soon.',
        
        // Payment settings
        'bank_name' => '',
        'bank_account_number' => '',
        'account_holder_name' => '',
        
        // SMTP settings
        'smtp_host' => 'smtp.gmail.com',
        'smtp_port' => 587,
        'smtp_username' => '',
        'smtp_password' => '',
        'smtp_encryption' => 'tls',
        
        // Limit settings
        'default_plan_id' => 0,
        'min_deposit' => 10000,
        'max_deposit' => 50000000,
    ];

    public function __construct()
    {
        $this->settingModel = new Setting();
    }

    /**
     * Get a setting value (uses cache)
     */
    public function get(string $key, $default = null): mixed
    {
        $this->ensureCacheLoaded();
        
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }
        
        // Use default from our defaults array if not provided
        if ($default === null && array_key_exists($key, self::$defaults)) {
            return self::$defaults[$key];
        }
        
        return $default;
    }

    /**
     * Set a setting value (updates cache and database)
     */
    public function set(string $key, $value, string $type = 'string'): bool
    {
        $result = $this->settingModel->set($key, $value, $type);
        
        if ($result) {
            self::$cache[$key] = $value;
        }
        
        return $result;
    }

    /**
     * Get all settings as an associative array (includes defaults)
     */
    public function getAll(): array
    {
        $this->ensureCacheLoaded();
        
        // Merge defaults with cached values (cached takes precedence)
        return array_merge(self::$defaults, self::$cache);
    }

    /**
     * Get all settings starting with a prefix
     */
    public function getGroup(string $prefix): array
    {
        $this->ensureCacheLoaded();
        
        $grouped = [];
        foreach (self::$cache as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                // Remove prefix for cleaner key names
                $shortKey = substr($key, strlen($prefix));
                $grouped[$shortKey] = $value;
            }
        }
        
        return $grouped;
    }

    /**
     * Clear and reload the cache
     */
    public function refreshCache(): void
    {
        self::$cache = $this->settingModel->getAll();
        self::$cacheLoaded = true;
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
     * Get general settings as a group
     */
    public function getGeneralSettings(): array
    {
        return [
            'site_name' => $this->get('site_name'),
            'site_url' => $this->get('site_url'),
            'logo_url' => $this->get('logo_url'),
            'favicon_url' => $this->get('favicon_url'),
        ];
    }

    /**
     * Get maintenance settings as a group
     */
    public function getMaintenanceSettings(): array
    {
        return [
            'maintenance_mode' => $this->get('maintenance_mode'),
            'maintenance_message' => $this->get('maintenance_message'),
        ];
    }

    /**
     * Get SMTP settings as a group
     */
    public function getSmtpSettings(): array
    {
        return [
            'host' => $this->get('smtp_host'),
            'port' => $this->get('smtp_port'),
            'username' => $this->get('smtp_username'),
            'password' => $this->get('smtp_password'),
            'encryption' => $this->get('smtp_encryption'),
        ];
    }

    /**
     * Get payment settings as a group
     */
    public function getPaymentSettings(): array
    {
        return [
            'bank_name' => $this->get('bank_name'),
            'bank_account_number' => $this->get('bank_account_number'),
            'account_holder_name' => $this->get('account_holder_name'),
        ];
    }

    /**
     * Get limit settings as a group
     */
    public function getLimitSettings(): array
    {
        return [
            'default_plan_id' => $this->get('default_plan_id'),
            'min_deposit' => $this->get('min_deposit'),
            'max_deposit' => $this->get('max_deposit'),
        ];
    }

    /**
     * Ensure cache is loaded
     */
    private function ensureCacheLoaded(): void
    {
        if (!self::$cacheLoaded) {
            $this->refreshCache();
        }
    }
}
