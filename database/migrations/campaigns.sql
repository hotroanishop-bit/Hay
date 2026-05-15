-- Campaigns Migration
-- Registration campaigns with bonus tokens/credits

-- Main campaigns table
CREATE TABLE IF NOT EXISTS campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL COMMENT 'URL slug: /register?campaign=slug',
    description TEXT,
    bonus_tokens DECIMAL(10,2) DEFAULT 0 COMMENT 'Token bonus khi dang ky',
    bonus_credits DECIMAL(10,2) DEFAULT 0 COMMENT 'Credits bonus',
    max_registrations INT DEFAULT 0 COMMENT '0 = unlimited',
    current_registrations INT DEFAULT 0,
    starts_at DATETIME DEFAULT NULL,
    expires_at DATETIME DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_by INT UNSIGNED,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_campaigns_slug (slug),
    INDEX idx_campaigns_active (is_active, starts_at, expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Campaign registrations tracking table
CREATE TABLE IF NOT EXISTS campaign_registrations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    bonus_received DECIMAL(10,2) DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    UNIQUE KEY unique_campaign_user (campaign_id, user_id),
    INDEX idx_campaign_registrations_campaign (campaign_id),
    INDEX idx_campaign_registrations_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
