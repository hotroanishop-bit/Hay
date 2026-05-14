-- Migration: Coupon/Promo Code System
-- Tables: coupons, coupon_usages
-- Run this after referral_system.sql

-- -----------------------------------------------------
-- Table: coupons
-- Stores coupon/promo code definitions
-- -----------------------------------------------------
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

-- -----------------------------------------------------
-- Table: coupon_usages
-- Tracks coupon usage by users
-- -----------------------------------------------------
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
    INDEX idx_coupon_usages_coupon_user (coupon_id, user_id),
    
    CONSTRAINT fk_coupon_usages_coupon_id 
        FOREIGN KEY (coupon_id) REFERENCES coupons(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_coupon_usages_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_coupon_usages_deposit_id 
        FOREIGN KEY (deposit_id) REFERENCES deposits(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Add coupon_id column to deposits table
-- -----------------------------------------------------
ALTER TABLE deposits
ADD COLUMN coupon_id INT UNSIGNED NULL AFTER qr_data,
ADD COLUMN discount_amount DECIMAL(15,2) DEFAULT 0.00 AFTER coupon_id,
ADD COLUMN bonus_amount DECIMAL(15,2) DEFAULT 0.00 AFTER discount_amount,
ADD INDEX idx_deposits_coupon_id (coupon_id),
ADD CONSTRAINT fk_deposits_coupon_id 
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) 
    ON DELETE SET NULL ON UPDATE CASCADE;
