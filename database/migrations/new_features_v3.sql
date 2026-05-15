-- New Features v3 Migration
-- Leaderboard, Live Chat, Feedback, Enhanced Analytics

-- =====================================================
-- LIVE CHAT SYSTEM
-- =====================================================

CREATE TABLE IF NOT EXISTS chat_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    admin_id INT UNSIGNED NULL,
    message TEXT NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL DEFAULT 'user',
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_chat_messages_user_id (user_id),
    INDEX idx_chat_messages_admin_id (admin_id),
    INDEX idx_chat_messages_sender_type (sender_type),
    INDEX idx_chat_messages_is_read (is_read),
    INDEX idx_chat_messages_created_at (created_at),
    
    CONSTRAINT fk_chat_messages_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_chat_messages_admin_id 
        FOREIGN KEY (admin_id) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USER FEEDBACK/RATING SYSTEM
-- =====================================================

CREATE TABLE IF NOT EXISTS user_feedback (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
    category ENUM('api_quality', 'speed', 'support', 'overall', 'other') NOT NULL DEFAULT 'overall',
    comment TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_feedback_user_id (user_id),
    INDEX idx_feedback_rating (rating),
    INDEX idx_feedback_category (category),
    INDEX idx_feedback_created_at (created_at),
    
    CONSTRAINT fk_feedback_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- LEADERBOARD CACHE (for performance)
-- =====================================================

CREATE TABLE IF NOT EXISTS leaderboard_cache (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL COMMENT 'api_calls, spending, referrals, checkin_streak',
    period VARCHAR(20) NOT NULL COMMENT 'weekly, monthly, all_time',
    user_id INT UNSIGNED NOT NULL,
    rank_position INT UNSIGNED NOT NULL,
    score DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_leaderboard_type (type),
    INDEX idx_leaderboard_period (period),
    INDEX idx_leaderboard_user_id (user_id),
    INDEX idx_leaderboard_rank (rank_position),
    INDEX idx_leaderboard_type_period (type, period),
    UNIQUE KEY unique_type_period_user (type, period, user_id),
    
    CONSTRAINT fk_leaderboard_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USER SEARCH HISTORY
-- =====================================================

CREATE TABLE IF NOT EXISTS search_history (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    query VARCHAR(255) NOT NULL,
    result_type VARCHAR(50) NULL COMMENT 'page, api_key, transaction',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_search_history_user_id (user_id),
    INDEX idx_search_history_created_at (created_at),
    
    CONSTRAINT fk_search_history_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
