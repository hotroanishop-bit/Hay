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
     * Get SMTP settings as a group
     */
    public function getSmtpSettings(): array
    {
        return [
            'host' => $this->get('smtp_host', 'smtp.gmail.com'),
            'port' => $this->get('smtp_port', 587),
            'username' => $this->get('smtp_username', ''),
            'password' => $this->get('smtp_password', ''),
            'encryption' => $this->get('smtp_encryption', 'tls')
        ];
    }

    /**
     * Get payment settings as a group
     */
    public function getPaymentSettings(): array
    {
        return [
            'bank_name' => $this->get('bank_name', ''),
            'bank_account_number' => $this->get('bank_account_number', ''),
            'account_holder_name' => $this->get('account_holder_name', ''),
            'min_deposit' => $this->get('min_deposit', 10000),
            'max_deposit' => $this->get('max_deposit', 50000000)
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
