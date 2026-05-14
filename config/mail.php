<?php
/**
 * Mail Configuration
 * Settings for email notifications
 */

return [
    // Mail driver: smtp, sendmail, mail
    'driver' => getenv('MAIL_DRIVER') ?: 'smtp',
    
    // SMTP settings
    'host' => getenv('MAIL_HOST') ?: 'smtp.mailtrap.io',
    'port' => getenv('MAIL_PORT') ?: 587,
    'username' => getenv('MAIL_USERNAME') ?: '',
    'password' => getenv('MAIL_PASSWORD') ?: '',
    'encryption' => getenv('MAIL_ENCRYPTION') ?: 'tls',
    
    // Sender information
    'from' => [
        'address' => getenv('MAIL_FROM_ADDRESS') ?: 'noreply@example.com',
        'name' => getenv('MAIL_FROM_NAME') ?: 'Hay API Keys',
    ],
    
    // Reply-to address
    'reply_to' => [
        'address' => getenv('MAIL_REPLY_TO') ?: 'support@example.com',
        'name' => getenv('MAIL_REPLY_TO_NAME') ?: 'Support',
    ],
    
    // Email templates path
    'templates_path' => VIEWS_PATH . '/emails',
    
    // Queue emails (if queue system is implemented)
    'queue' => false,
];
