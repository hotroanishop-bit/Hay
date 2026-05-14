-- =============================================================================
-- COMBINED MIGRATION: All Features for Fresh Install
-- =============================================================================
-- This migration combines all feature migrations for easy deployment.
-- Run this on a fresh database after the base schema.sql
-- 
-- Order of operations:
-- 1. User table modifications (referral, security, telegram, roles)
-- 2. Referrals table
-- 3. Coupons and coupon_usages tables
-- 4. Webhooks and webhook_logs tables
-- 5. Changelogs table
-- 6. Login history and user sessions tables
-- 7. Email templates table
-- 8. Incidents and incident_updates tables
-- 9. Admin roles table
-- 10. Scheduled maintenance table
-- 11. Auto top-up settings table
-- 12. Telegram link tokens table
-- 13. Default data inserts
-- =============================================================================

-- Start transaction for atomicity
START TRANSACTION;

-- =============================================================================
-- SECTION 1: USER TABLE MODIFICATIONS
-- =============================================================================

-- Add referral columns
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS referral_code VARCHAR(20) NULL UNIQUE,
ADD COLUMN IF NOT EXISTS referred_by INT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS total_referral_earnings DECIMAL(15,2) DEFAULT 0.00;

-- Add security columns
ALTER TABLE users
ADD COLUMN IF NOT EXISTS failed_login_attempts INT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL DEFAULT NULL;

-- Add preferred language column
ALTER TABLE users
ADD COLUMN IF NOT EXISTS preferred_language VARCHAR(10) DEFAULT 'en';

-- Add telegram columns
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS telegram_chat_id VARCHAR(50) NULL,
ADD COLUMN IF NOT EXISTS telegram_linked_at TIMESTAMP NULL;

-- Add role column (will add foreign key after admin_roles table is created)
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS role_id INT NULL;

-- Add indexes for user columns (ignore if exists)
CREATE INDEX IF NOT EXISTS idx_users_referral_code ON users(referral_code);
CREATE INDEX IF NOT EXISTS idx_users_referred_by ON users(referred_by);
CREATE INDEX IF NOT EXISTS idx_users_locked_until ON users(locked_until);
CREATE INDEX IF NOT EXISTS idx_users_telegram_chat_id ON users(telegram_chat_id);

-- =============================================================================
-- SECTION 2: REFERRALS TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS referrals (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT UNSIGNED NOT NULL,
    referred_id INT UNSIGNED NOT NULL,
    commission_earned DECIMAL(15,2) DEFAULT 0.00,
    status ENUM('pending', 'approved', 'paid') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_referrals_referrer_id (referrer_id),
    INDEX idx_referrals_referred_id (referred_id),
    INDEX idx_referrals_status (status),
    INDEX idx_referrals_created_at (created_at),
    
    CONSTRAINT uq_referrals_referred_id UNIQUE (referred_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 3: COUPONS TABLES
-- =============================================================================

CREATE TABLE IF NOT EXISTS coupons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('percentage', 'fixed', 'bonus') NOT NULL DEFAULT 'percentage',
    value DECIMAL(15,2) NOT NULL,
    min_amount DECIMAL(15,2) DEFAULT 0.00,
    max_uses INT UNSIGNED NULL,
    used_count INT UNSIGNED DEFAULT 0,
    max_uses_per_user INT UNSIGNED DEFAULT 1,
    expires_at TIMESTAMP NULL,
    is_active TINYINT(1) DEFAULT 1,
    description TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_coupons_code (code),
    INDEX idx_coupons_is_active (is_active),
    INDEX idx_coupons_expires_at (expires_at),
    INDEX idx_coupons_type (type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS coupon_usages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    coupon_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    deposit_id INT UNSIGNED NULL,
    amount_saved DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    used_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_coupon_usages_coupon_id (coupon_id),
    INDEX idx_coupon_usages_user_id (user_id),
    INDEX idx_coupon_usages_deposit_id (deposit_id),
    INDEX idx_coupon_usages_used_at (used_at),
    INDEX idx_coupon_usages_coupon_user (coupon_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add coupon columns to deposits table
ALTER TABLE deposits
ADD COLUMN IF NOT EXISTS coupon_id INT UNSIGNED NULL,
ADD COLUMN IF NOT EXISTS discount_amount DECIMAL(15,2) DEFAULT 0.00,
ADD COLUMN IF NOT EXISTS bonus_amount DECIMAL(15,2) DEFAULT 0.00;

CREATE INDEX IF NOT EXISTS idx_deposits_coupon_id ON deposits(coupon_id);

-- =============================================================================
-- SECTION 4: WEBHOOKS TABLES
-- =============================================================================

CREATE TABLE IF NOT EXISTS webhooks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    url VARCHAR(500) NOT NULL,
    secret VARCHAR(64) NOT NULL,
    events JSON NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_webhooks_user_id (user_id),
    INDEX idx_webhooks_is_active (is_active),
    INDEX idx_webhooks_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS webhook_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    webhook_id INT UNSIGNED NOT NULL,
    event VARCHAR(100) NOT NULL,
    payload JSON NOT NULL,
    response_code INT NULL,
    response_body TEXT NULL,
    attempts INT UNSIGNED DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_webhook_logs_webhook_id (webhook_id),
    INDEX idx_webhook_logs_event (event),
    INDEX idx_webhook_logs_response_code (response_code),
    INDEX idx_webhook_logs_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 5: CHANGELOGS TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS changelogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('feature', 'fix', 'improvement', 'security') NOT NULL DEFAULT 'feature',
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_version (version),
    INDEX idx_type (type),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 6: LOGIN HISTORY AND USER SESSIONS
-- =============================================================================

CREATE TABLE IF NOT EXISTS login_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    location VARCHAR(255) DEFAULT NULL,
    success BOOLEAN NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_login_history_user_id (user_id),
    INDEX idx_login_history_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user_sessions_user_id (user_id),
    INDEX idx_user_sessions_token (token),
    INDEX idx_user_sessions_last_active (last_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 7: EMAIL TEMPLATES TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS email_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    subject VARCHAR(255) NOT NULL,
    body TEXT NOT NULL,
    variables JSON DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_name (name),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 8: INCIDENTS TABLES (STATUS PAGE)
-- =============================================================================

CREATE TABLE IF NOT EXISTS incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    status ENUM('investigating', 'identified', 'monitoring', 'resolved') DEFAULT 'investigating',
    severity ENUM('minor', 'major', 'critical') DEFAULT 'minor',
    affected_components JSON DEFAULT NULL,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    resolved_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_severity (severity),
    INDEX idx_started_at (started_at),
    INDEX idx_resolved_at (resolved_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS incident_updates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    incident_id INT NOT NULL,
    message TEXT NOT NULL,
    status ENUM('investigating', 'identified', 'monitoring', 'resolved') DEFAULT 'investigating',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_incident_id (incident_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 9: ADMIN ROLES TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS admin_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    permissions JSON NOT NULL,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 10: SCHEDULED MAINTENANCE TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS scheduled_maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    starts_at TIMESTAMP NOT NULL,
    ends_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    show_countdown BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active_time (is_active, starts_at, ends_at),
    INDEX idx_starts_at (starts_at),
    INDEX idx_ends_at (ends_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 11: AUTO TOP-UP SETTINGS TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS auto_topup_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    threshold DECIMAL(10, 2) NOT NULL DEFAULT 10.00,
    amount DECIMAL(10, 2) NOT NULL DEFAULT 50.00,
    is_active BOOLEAN DEFAULT FALSE,
    last_triggered_at TIMESTAMP NULL,
    cooldown_hours INT DEFAULT 24,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_active_threshold (is_active, threshold)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 12: TELEGRAM LINK TOKENS TABLE
-- =============================================================================

CREATE TABLE IF NOT EXISTS telegram_link_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =============================================================================
-- SECTION 13: DEFAULT DATA INSERTS
-- =============================================================================

-- Insert default admin roles
INSERT INTO admin_roles (name, description, permissions, is_system) VALUES
(
    'super_admin',
    'Full system access with all permissions',
    '["users.view","users.edit","users.ban","users.balance","deposits.view","deposits.approve","deposits.reject","tickets.view","tickets.reply","tickets.close","settings.view","settings.edit","plans.manage","providers.manage","coupons.manage","roles.manage","impersonate","health.view"]',
    1
),
(
    'admin',
    'Standard admin with most permissions',
    '["users.view","users.edit","users.ban","users.balance","deposits.view","deposits.approve","deposits.reject","tickets.view","tickets.reply","tickets.close","settings.view","plans.manage","providers.manage","coupons.manage","health.view"]',
    1
),
(
    'moderator',
    'Limited admin for user support and moderation',
    '["users.view","tickets.view","tickets.reply","tickets.close","deposits.view"]',
    1
)
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Insert default referral commission rate setting
INSERT INTO settings (setting_key, setting_value, setting_type) 
VALUES ('referral_commission_rate', '0.05', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;

-- Insert sample changelog entries
INSERT INTO changelogs (version, title, description, type, published_at) VALUES
('1.0.0', 'Initial Release', 'First public release of the Hay API Gateway platform.', 'feature', NOW()),
('1.0.0', 'User Authentication', 'Secure login and registration with email verification.', 'feature', NOW()),
('1.0.0', 'API Key Management', 'Create, rotate, and revoke API keys with usage tracking.', 'feature', NOW()),
('1.1.0', 'Multi-language Support', 'Added Vietnamese and English language support.', 'feature', NOW()),
('1.1.0', 'Enhanced Security', 'Implemented stronger password hashing and 2FA support.', 'security', NOW()),
('1.2.0', 'Referral System', 'Earn commissions by referring new users.', 'feature', NOW()),
('1.2.0', 'Webhook Notifications', 'Real-time notifications for API events.', 'feature', NOW())
ON DUPLICATE KEY UPDATE title = VALUES(title);

-- Insert default email templates
INSERT INTO email_templates (name, subject, body, variables, is_active) VALUES
(
    'welcome',
    'Welcome to Hay API Gateway, {{user_name}}!',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;"><h1 style="color: #6366f1;">Welcome to Hay API Gateway!</h1><p>Hi {{user_name}},</p><p>Thank you for joining Hay API Gateway. We''re excited to have you on board!</p></div>',
    '["user_name", "user_email"]',
    1
),
(
    'deposit_approved',
    'Deposit Approved - ${{amount}} Added',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;"><h1 style="color: #22c55e;">Deposit Approved!</h1><p>Hi {{user_name}},</p><p>Your deposit of ${{amount}} has been approved and credited to your account.</p></div>',
    '["user_name", "amount", "reference_code", "new_balance"]',
    1
),
(
    'password_reset',
    'Reset Your Password',
    '<div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;"><h1 style="color: #6366f1;">Password Reset Request</h1><p>Hi {{user_name}},</p><p>Click the link below to reset your password:</p><p><a href="{{reset_url}}">Reset Password</a></p></div>',
    '["user_name", "reset_url", "expiry_hours"]',
    1
)
ON DUPLICATE KEY UPDATE subject = VALUES(subject);

-- Commit transaction
COMMIT;

-- =============================================================================
-- END OF COMBINED MIGRATION
-- =============================================================================
