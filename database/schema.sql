-- Hay API Keys Management System
-- Database Schema for MySQL

-- Create database (if not exists)
CREATE DATABASE IF NOT EXISTS hay_apikeys CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hay_apikeys;

-- -----------------------------------------------------
-- Table: users
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    balance DECIMAL(10, 2) DEFAULT 0.00,
    is_admin TINYINT(1) DEFAULT 0,
    two_factor_secret VARCHAR(255) NULL,
    two_factor_enabled TINYINT(1) DEFAULT 0,
    email_verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_users_email (email),
    INDEX idx_users_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: api_keys
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS api_keys (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    key_hash VARCHAR(64) NOT NULL UNIQUE,
    name VARCHAR(255) NOT NULL,
    provider VARCHAR(50) NOT NULL,
    model VARCHAR(100) NULL,
    rate_limit INT UNSIGNED DEFAULT 1000,
    usage_count INT UNSIGNED DEFAULT 0,
    usage_limit INT UNSIGNED DEFAULT 100000,
    is_active TINYINT(1) DEFAULT 1,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_api_keys_user_id (user_id),
    INDEX idx_api_keys_key_hash (key_hash),
    INDEX idx_api_keys_provider (provider),
    INDEX idx_api_keys_is_active (is_active),
    
    CONSTRAINT fk_api_keys_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: transactions
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type ENUM('credit', 'debit') NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    description VARCHAR(500) NULL,
    reference_id VARCHAR(100) NULL,
    payment_method VARCHAR(50) NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_transactions_user_id (user_id),
    INDEX idx_transactions_type (type),
    INDEX idx_transactions_status (status),
    INDEX idx_transactions_reference_id (reference_id),
    INDEX idx_transactions_created_at (created_at),
    
    CONSTRAINT fk_transactions_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: usage_logs
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usage_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    api_key_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    endpoint VARCHAR(255) NOT NULL,
    tokens_used INT UNSIGNED DEFAULT 0,
    cost DECIMAL(10, 6) DEFAULT 0.000000,
    ip_address VARCHAR(45) NULL,
    response_code SMALLINT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_usage_logs_api_key_id (api_key_id),
    INDEX idx_usage_logs_user_id (user_id),
    INDEX idx_usage_logs_endpoint (endpoint),
    INDEX idx_usage_logs_created_at (created_at),
    INDEX idx_usage_logs_user_date (user_id, created_at),
    
    CONSTRAINT fk_usage_logs_api_key_id 
        FOREIGN KEY (api_key_id) REFERENCES api_keys(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_usage_logs_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: tickets
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('open', 'pending', 'closed') DEFAULT 'open',
    priority ENUM('low', 'medium', 'high') DEFAULT 'medium',
    admin_reply TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_tickets_user_id (user_id),
    INDEX idx_tickets_status (status),
    INDEX idx_tickets_priority (priority),
    INDEX idx_tickets_created_at (created_at),
    
    CONSTRAINT fk_tickets_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Sample admin user (password: admin123)
-- -----------------------------------------------------
-- INSERT INTO users (email, password_hash, name, is_admin, email_verified_at) 
-- VALUES ('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', 1, NOW());
