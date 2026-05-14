-- Auto Top-Up Migration
-- Creates the auto_topup_settings table for automatic deposit triggers

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
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active),
    INDEX idx_active_threshold (is_active, threshold)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
