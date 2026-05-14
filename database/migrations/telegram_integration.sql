-- Telegram Integration Migration
-- Adds telegram_chat_id column to users table for Telegram notifications

ALTER TABLE users ADD COLUMN telegram_chat_id VARCHAR(50) NULL AFTER phone;
ALTER TABLE users ADD COLUMN telegram_linked_at TIMESTAMP NULL AFTER telegram_chat_id;
ALTER TABLE users ADD INDEX idx_telegram_chat_id (telegram_chat_id);

-- Telegram link tokens table for secure account linking
CREATE TABLE IF NOT EXISTS telegram_link_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(64) NOT NULL UNIQUE,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
