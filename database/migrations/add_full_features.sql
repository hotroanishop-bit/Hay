-- Migration: Add Full User and Admin Features
-- Tables: deposits, settings, audit_logs, password_resets, email_verifications
-- Also adds is_banned and avatar_url columns to users table

-- -----------------------------------------------------
-- Table: deposits
-- Manual deposit requests with bank transfer / QR payment
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS deposits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    reference_code VARCHAR(50) NOT NULL UNIQUE,
    bank_account VARCHAR(50) NULL,
    status ENUM('pending', 'approved', 'rejected', 'expired') DEFAULT 'pending',
    qr_data TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    processed_at TIMESTAMP NULL,
    processed_by INT UNSIGNED NULL,
    
    INDEX idx_deposits_user_id (user_id),
    INDEX idx_deposits_status (status),
    INDEX idx_deposits_reference_code (reference_code),
    INDEX idx_deposits_created_at (created_at),
    INDEX idx_deposits_processed_by (processed_by),
    
    CONSTRAINT fk_deposits_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_deposits_processed_by 
        FOREIGN KEY (processed_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: settings
-- Application settings stored in database
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT NULL,
    setting_type ENUM('string', 'int', 'bool', 'json') DEFAULT 'string',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_settings_setting_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: audit_logs
-- Admin action logging for security and compliance
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    admin_id INT UNSIGNED NOT NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NULL,
    target_id INT UNSIGNED NULL,
    old_value JSON NULL,
    new_value JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_audit_logs_admin_id (admin_id),
    INDEX idx_audit_logs_action (action),
    INDEX idx_audit_logs_target_type (target_type),
    INDEX idx_audit_logs_created_at (created_at),
    INDEX idx_audit_logs_target (target_type, target_id),
    
    CONSTRAINT fk_audit_logs_admin_id 
        FOREIGN KEY (admin_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: password_resets
-- Password reset tokens for forgot password flow
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS password_resets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_password_resets_email (email),
    INDEX idx_password_resets_token_hash (token_hash)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: email_verifications
-- Email verification tokens for new users
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS email_verifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_email_verifications_user_id (user_id),
    INDEX idx_email_verifications_token_hash (token_hash),
    
    CONSTRAINT fk_email_verifications_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Alter users table: Add is_banned and avatar_url columns
-- -----------------------------------------------------
ALTER TABLE users 
    ADD COLUMN is_banned TINYINT(1) DEFAULT 0 AFTER email_verified_at,
    ADD COLUMN avatar_url VARCHAR(500) NULL AFTER is_banned,
    ADD INDEX idx_users_is_banned (is_banned);

-- -----------------------------------------------------
-- Insert default settings
-- Site configuration
-- -----------------------------------------------------
INSERT INTO settings (setting_key, setting_value, setting_type) VALUES
    ('site_name', 'Hay API Keys', 'string'),
    ('site_url', 'http://localhost', 'string'),
    ('logo_url', '', 'string'),
    ('favicon_url', '', 'string'),
    ('maintenance_mode', '0', 'bool'),
    ('maintenance_message', 'We are currently performing maintenance. Please check back soon.', 'string'),
    ('bank_name', 'Vietcombank', 'string'),
    ('bank_account_number', '', 'string'),
    ('account_holder_name', '', 'string'),
    ('smtp_host', 'smtp.gmail.com', 'string'),
    ('smtp_port', '587', 'int'),
    ('smtp_username', '', 'string'),
    ('smtp_password', '', 'string'),
    ('smtp_encryption', 'tls', 'string'),
    ('default_plan_id', '1', 'int'),
    ('min_deposit', '10000', 'int'),
    ('max_deposit', '50000000', 'int');
