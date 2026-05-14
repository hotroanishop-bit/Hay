<?php
/**
 * Application Configuration
 * General application-wide settings
 */

return [
    // Application name
    'name' => getenv('APP_NAME') ?: 'Hay API Keys',
    
    // Application URL
    'url' => getenv('APP_URL') ?: 'http://localhost',
    
    // Debug mode
    'debug' => getenv('APP_DEBUG') === 'true' ?: false,
    
    // Timezone
    'timezone' => getenv('APP_TIMEZONE') ?: 'UTC',
    
    // Default locale
    'locale' => 'en',
    
    // Encryption key for sessions and tokens
    'key' => getenv('APP_KEY') ?: 'base64:change-this-to-a-secure-random-key',
    
    // Session configuration
    'session' => [
        'lifetime' => 120, // minutes
        'secure' => false,
        'http_only' => true,
        'same_site' => 'lax',
    ],
    
    // Pagination
    'per_page' => 15,
    
    // API Key settings
    'api_key' => [
        'prefix' => 'hay_',
        'length' => 32,
        'default_rate_limit' => 1000, // requests per day
        'default_usage_limit' => 100000, // total requests
    ],
    
    // Currency settings
    'currency' => [
        'code' => 'USD',
        'symbol' => '$',
        'decimals' => 2,
    ],
];
