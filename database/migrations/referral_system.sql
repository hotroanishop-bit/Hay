-- Migration: Referral/Affiliate System
-- Tables: referrals
-- Also adds referral columns to users table
-- Run this after add_full_features.sql

-- -----------------------------------------------------
-- Add referral columns to users table
-- -----------------------------------------------------
ALTER TABLE users 
ADD COLUMN referral_code VARCHAR(20) NULL UNIQUE AFTER is_banned,
ADD COLUMN referred_by INT UNSIGNED NULL AFTER referral_code,
ADD COLUMN total_referral_earnings DECIMAL(15,2) DEFAULT 0.00 AFTER referred_by,
ADD INDEX idx_users_referral_code (referral_code),
ADD INDEX idx_users_referred_by (referred_by),
ADD CONSTRAINT fk_users_referred_by 
    FOREIGN KEY (referred_by) REFERENCES users(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Table: referrals
-- Tracks referral relationships and commissions
-- -----------------------------------------------------
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
    
    CONSTRAINT fk_referrals_referrer_id 
        FOREIGN KEY (referrer_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_referrals_referred_id 
        FOREIGN KEY (referred_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_referrals_referred_id 
        UNIQUE (referred_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Insert default referral commission rate setting
-- -----------------------------------------------------
INSERT INTO settings (setting_key, setting_value, setting_type) 
VALUES ('referral_commission_rate', '0.05', 'string')
ON DUPLICATE KEY UPDATE setting_value = setting_value;
