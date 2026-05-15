-- Migration: System Settings Table
-- Enhanced settings table with groups, labels, descriptions, and sensitive flag
-- Allows admin to manage ALL settings via Admin Panel

-- -----------------------------------------------------
-- Table: system_settings
-- Comprehensive system configuration
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS system_settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'number', 'boolean', 'json', 'text') DEFAULT 'string',
    setting_group VARCHAR(50) DEFAULT 'general',
    label VARCHAR(255) NULL COMMENT 'Display label in admin UI',
    description TEXT NULL COMMENT 'Help text for the setting',
    is_sensitive TINYINT(1) DEFAULT 0 COMMENT 'Hide value in UI (passwords, keys)',
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    updated_by INT UNSIGNED NULL,
    
    INDEX idx_settings_group (setting_group),
    INDEX idx_settings_key (setting_key),
    
    CONSTRAINT fk_system_settings_updated_by 
        FOREIGN KEY (updated_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Insert default settings
-- Organized by groups for easy management
-- -----------------------------------------------------

-- App Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('app_name', 'Hay API Gateway', 'string', 'app', 'Site Name', 'Ten website hien thi', 0),
('app_url', 'https://yourdomain.com', 'string', 'app', 'Site URL', 'URL chinh cua website', 0),
('app_logo', '/assets/img/logo.png', 'string', 'app', 'Logo URL', 'Duong dan logo', 0),
('app_favicon', '/assets/img/favicon.ico', 'string', 'app', 'Favicon URL', 'Duong dan favicon', 0),
('app_debug', '0', 'boolean', 'app', 'Debug Mode', 'Bat che do debug (chi development)', 0);

-- Database Settings (read-only display)
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('db_host', 'localhost', 'string', 'database', 'Database Host', 'Host MySQL', 0),
('db_name', 'hay_gateway', 'string', 'database', 'Database Name', 'Ten database', 0);

-- Mail Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('mail_host', 'smtp.gmail.com', 'string', 'mail', 'SMTP Host', 'Server SMTP', 0),
('mail_port', '587', 'number', 'mail', 'SMTP Port', 'Port SMTP (587 cho TLS)', 0),
('mail_username', '', 'string', 'mail', 'SMTP Username', 'Email gui mail', 0),
('mail_password', '', 'string', 'mail', 'SMTP Password', 'App password', 1),
('mail_encryption', 'tls', 'string', 'mail', 'Encryption', 'TLS hoac SSL', 0),
('mail_from_name', 'Hay API Gateway', 'string', 'mail', 'From Name', 'Ten hien thi khi gui mail', 0),
('mail_from_address', '', 'string', 'mail', 'From Address', 'Email hien thi khi gui', 0);

-- Telegram Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('telegram_bot_token', '', 'string', 'telegram', 'Bot Token', 'Token cua Telegram Bot', 1),
('telegram_admin_chat_id', '', 'string', 'telegram', 'Admin Chat ID', 'Chat ID de nhan thong bao admin', 0),
('telegram_enabled', '0', 'boolean', 'telegram', 'Enable Telegram', 'Bat thong bao Telegram', 0);

-- Payment/VietQR Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('vietqr_bank_id', 'MB', 'string', 'payment', 'Bank ID', 'Ma ngan hang (MB, VCB, TCB...)', 0),
('vietqr_account_no', '', 'string', 'payment', 'Account Number', 'So tai khoan ngan hang', 0),
('vietqr_account_name', '', 'string', 'payment', 'Account Name', 'Ten chu tai khoan', 0),
('payment_min_deposit', '10000', 'number', 'payment', 'Min Deposit', 'So tien nap toi thieu (VND)', 0),
('payment_max_deposit', '50000000', 'number', 'payment', 'Max Deposit', 'So tien nap toi da (VND)', 0);

-- API Gateway Settings
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('api_upstream_url', 'https://api.openai.com', 'string', 'api', 'Upstream URL', 'URL cua provider API', 0),
('api_upstream_key', '', 'string', 'api', 'Upstream API Key', 'API key cua provider', 1),
('api_proxy_host', '', 'string', 'api', 'Proxy Host', 'Proxy host (neu co)', 0),
('api_proxy_port', '', 'string', 'api', 'Proxy Port', 'Proxy port', 0),
('api_proxy_user', '', 'string', 'api', 'Proxy Username', 'Proxy auth username', 0),
('api_proxy_pass', '', 'string', 'api', 'Proxy Password', 'Proxy auth password', 1),
('api_retry_count', '1', 'number', 'api', 'Retry Count', 'So lan retry khi loi', 0),
('api_timeout', '60', 'number', 'api', 'Timeout', 'Timeout (giay)', 0);

-- Rate Limits
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('rate_limit_default', '60', 'number', 'limits', 'Default Rate Limit', 'Request/phut mac dinh', 0),
('rate_limit_daily', '1000', 'number', 'limits', 'Daily Limit', 'Request/ngay mac dinh', 0),
('max_api_keys', '10', 'number', 'limits', 'Max API Keys', 'So key toi da moi user', 0),
('session_timeout', '120', 'number', 'limits', 'Session Timeout', 'Timeout phien (phut)', 0);

-- Maintenance
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('maintenance_mode', '0', 'boolean', 'maintenance', 'Maintenance Mode', 'Bat che do bao tri', 0),
('maintenance_message', 'Website dang bao tri, vui long quay lai sau.', 'text', 'maintenance', 'Maintenance Message', 'Thong bao hien thi', 0);

-- Registration
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('registration_enabled', '1', 'boolean', 'registration', 'Enable Registration', 'Cho phep dang ky', 0),
('default_user_balance', '0', 'number', 'registration', 'Default Balance', 'So du mac dinh user moi', 0),
('default_plan_id', '1', 'number', 'registration', 'Default Plan', 'Plan mac dinh cho user moi', 0),
('welcome_bonus', '0', 'number', 'registration', 'Welcome Bonus', 'Token tang khi dang ky', 0);

-- Check-in Rewards
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('checkin_base_reward', '10', 'number', 'rewards', 'Base Check-in Reward', 'Token thuong co ban', 0),
('checkin_streak_bonus', '10', 'number', 'rewards', 'Streak Bonus %', 'Bonus % moi ngay lien tiep', 0),
('checkin_max_multiplier', '2', 'number', 'rewards', 'Max Multiplier', 'He so nhan toi da', 0);

-- Referral
INSERT INTO system_settings (setting_key, setting_value, setting_type, setting_group, label, description, is_sensitive) VALUES
('referral_enabled', '1', 'boolean', 'referral', 'Enable Referral', 'Bat he thong gioi thieu', 0),
('referral_commission', '10', 'number', 'referral', 'Commission %', 'Phan tram hoa hong', 0),
('referral_bonus', '0', 'number', 'referral', 'Signup Bonus', 'Token thuong nguoi duoc gioi thieu', 0);
