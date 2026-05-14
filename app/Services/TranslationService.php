<?php
/**
 * TranslationService
 * Handles language loading, switching, and translation retrieval.
 * Implements singleton pattern for easy global access.
 */
class TranslationService
{
    /**
     * Singleton instance
     */
    private static ?TranslationService $instance = null;
    
    /**
     * Current language code
     */
    private string $currentLanguage = 'en';
    
    /**
     * Available languages
     */
    private array $availableLanguages = ['en', 'vi'];
    
    /**
     * Default language
     */
    private string $defaultLanguage = 'en';
    
    /**
     * Loaded translations
     */
    private array $translations = [];
    
    /**
     * Path to language files
     */
    private string $langPath;
    
    /**
     * Private constructor for singleton
     */
    private function __construct()
    {
        $this->langPath = ROOT_PATH . '/lang';
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance(): TranslationService
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Initialize the translation service
     * Determines language from URL param, session, user preference, or default
     */
    public function initialize(): void
    {
        // Priority: 1) URL param, 2) Session, 3) User preference, 4) Default
        $lang = null;
        
        // 1. Check URL parameter
        if (isset($_GET['lang']) && $this->isValidLanguage($_GET['lang'])) {
            $lang = $_GET['lang'];
            // Save to session when changed via URL
            $_SESSION['lang'] = $lang;
        }
        
        // 2. Check session
        if ($lang === null && isset($_SESSION['lang']) && $this->isValidLanguage($_SESSION['lang'])) {
            $lang = $_SESSION['lang'];
        }
        
        // 3. Check user preference (if logged in)
        if ($lang === null && isset($_SESSION['user']['preferred_language']) && $this->isValidLanguage($_SESSION['user']['preferred_language'])) {
            $lang = $_SESSION['user']['preferred_language'];
        }
        
        // 4. Use default
        if ($lang === null) {
            $lang = $this->defaultLanguage;
        }
        
        $this->setLanguage($lang);
    }
    
    /**
     * Check if a language code is valid
     */
    public function isValidLanguage(string $lang): bool
    {
        return in_array($lang, $this->availableLanguages, true);
    }
    
    /**
     * Load language file
     */
    public function loadLanguage(string $lang): array
    {
        if (!$this->isValidLanguage($lang)) {
            $lang = $this->defaultLanguage;
        }
        
        $file = $this->langPath . '/' . $lang . '.php';
        
        if (file_exists($file)) {
            return require $file;
        }
        
        return [];
    }
    
    /**
     * Set current language
     */
    public function setLanguage(string $lang): void
    {
        if (!$this->isValidLanguage($lang)) {
            $lang = $this->defaultLanguage;
        }
        
        $this->currentLanguage = $lang;
        $this->translations = $this->loadLanguage($lang);
        
        // Save to session
        $_SESSION['lang'] = $lang;
    }
    
    /**
     * Get current language
     */
    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }
    
    /**
     * Get available languages
     */
    public function getAvailableLanguages(): array
    {
        return $this->availableLanguages;
    }
    
    /**
     * Get a translation by dot notation key
     * Example: get('auth.login', 'Login')
     */
    public function get(string $key, ?string $default = null): string
    {
        $keys = explode('.', $key);
        $value = $this->translations;
        
        foreach ($keys as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];
            } else {
                return $default ?? $key;
            }
        }
        
        if (is_string($value)) {
            return $value;
        }
        
        return $default ?? $key;
    }
    
    /**
     * Get language display name
     */
    public function getLanguageName(string $code): string
    {
        $names = [
            'en' => 'English',
            'vi' => 'Tieng Viet',
        ];
        
        return $names[$code] ?? $code;
    }
    
    /**
     * Get HTML lang attribute value
     */
    public function getHtmlLang(): string
    {
        $mapping = [
            'en' => 'en',
            'vi' => 'vi',
        ];
        
        return $mapping[$this->currentLanguage] ?? 'en';
    }
}
