<?php
/**
 * Translation Helper Functions
 * Global helper functions for easy translation access throughout views
 */

/**
 * Get a translated string
 * Shorthand for TranslationService::getInstance()->get()
 * 
 * @param string $key Dot notation key (e.g., 'auth.login', 'buttons.submit')
 * @param string|null $default Default value if key not found
 * @return string Translated string or default
 */
function __($key, $default = null)
{
    return TranslationService::getInstance()->get($key, $default);
}

/**
 * Alias for __() function
 * Some developers prefer 'trans' as a function name
 * 
 * @param string $key Dot notation key
 * @param string|null $default Default value if key not found
 * @return string Translated string or default
 */
function trans($key, $default = null)
{
    return __($key, $default);
}

/**
 * Get current language code
 * 
 * @return string Language code (e.g., 'en', 'vi')
 */
function currentLang()
{
    return TranslationService::getInstance()->getCurrentLanguage();
}

/**
 * Get HTML lang attribute value
 * 
 * @return string HTML lang attribute value
 */
function htmlLang()
{
    return TranslationService::getInstance()->getHtmlLang();
}

/**
 * Echo a translated string (with HTML escaping)
 * Useful in views where you want to echo directly
 * 
 * @param string $key Dot notation key
 * @param string|null $default Default value if key not found
 */
function _e($key, $default = null)
{
    echo htmlspecialchars(__($key, $default), ENT_QUOTES, 'UTF-8');
}

/**
 * Get available languages
 * 
 * @return array Available language codes
 */
function availableLanguages()
{
    return TranslationService::getInstance()->getAvailableLanguages();
}

/**
 * Get language display name
 * 
 * @param string $code Language code
 * @return string Display name
 */
function languageName($code)
{
    return TranslationService::getInstance()->getLanguageName($code);
}
