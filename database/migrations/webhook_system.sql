-- Migration: Webhook Notifications System
-- Tables: webhooks, webhook_logs
-- Run this after coupon_system.sql

-- -----------------------------------------------------
-- Table: webhooks
-- Stores user webhook configurations
-- -----------------------------------------------------
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
    INDEX idx_webhooks_created_at (created_at),
    
    CONSTRAINT fk_webhooks_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: webhook_logs
-- Tracks webhook delivery attempts and results
-- -----------------------------------------------------
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
    INDEX idx_webhook_logs_created_at (created_at),
    
    CONSTRAINT fk_webhook_logs_webhook_id 
        FOREIGN KEY (webhook_id) REFERENCES webhooks(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
