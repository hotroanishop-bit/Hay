<?php
/**
 * Telegram Bot Configuration
 * Configure your Telegram bot settings here
 */

return [
    // Bot token from @BotFather
    'bot_token' => getenv('TELEGRAM_BOT_TOKEN') ?: '',
    
    // Bot username (without @)
    'bot_username' => getenv('TELEGRAM_BOT_USERNAME') ?: '',
    
    // Webhook URL (optional - for production use)
    'webhook_url' => getenv('TELEGRAM_WEBHOOK_URL') ?: '',
    
    // Webhook secret (for webhook verification)
    'webhook_secret' => getenv('TELEGRAM_WEBHOOK_SECRET') ?: '',
    
    // Link token expiry time in minutes
    'link_token_expiry' => 30,
    
    // API Base URL
    'api_base_url' => 'https://api.telegram.org/bot',
    
    // Enable/disable Telegram notifications globally
    'enabled' => (bool) (getenv('TELEGRAM_ENABLED') ?: false),
    
    // Notification types
    'notifications' => [
        'deposit_approved' => true,
        'deposit_rejected' => true,
        'low_balance' => true,
        'api_key_created' => true,
        'security_alert' => true,
    ],
];
