-- New Features v2 Migration
-- Gift Codes, Daily Check-in, Achievements, Enhanced Tickets, Notifications, Favorites, Alerts, Key Templates

-- =====================================================
-- GIFT CODES SYSTEM
-- =====================================================

-- Gift codes table
CREATE TABLE IF NOT EXISTS gift_codes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    type ENUM('tokens', 'credits', 'plan', 'vip_days') NOT NULL DEFAULT 'tokens',
    value DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    max_uses INT UNSIGNED NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    used_count INT UNSIGNED NOT NULL DEFAULT 0,
    expires_at DATETIME NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_gift_codes_code (code),
    INDEX idx_gift_codes_is_active (is_active),
    INDEX idx_gift_codes_expires_at (expires_at),
    INDEX idx_gift_codes_created_by (created_by),
    
    CONSTRAINT fk_gift_codes_created_by 
        FOREIGN KEY (created_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Gift code redemptions table
CREATE TABLE IF NOT EXISTS gift_code_redemptions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    gift_code_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    value_received DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_redemptions_gift_code_id (gift_code_id),
    INDEX idx_redemptions_user_id (user_id),
    INDEX idx_redemptions_redeemed_at (redeemed_at),
    UNIQUE KEY unique_user_code (gift_code_id, user_id),
    
    CONSTRAINT fk_redemptions_gift_code_id 
        FOREIGN KEY (gift_code_id) REFERENCES gift_codes(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_redemptions_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DAILY CHECK-IN SYSTEM
-- =====================================================

CREATE TABLE IF NOT EXISTS daily_checkins (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    checkin_date DATE NOT NULL,
    streak_count INT UNSIGNED NOT NULL DEFAULT 1,
    reward_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_checkins_user_id (user_id),
    INDEX idx_checkins_checkin_date (checkin_date),
    INDEX idx_checkins_user_date (user_id, checkin_date),
    UNIQUE KEY unique_user_checkin (user_id, checkin_date),
    
    CONSTRAINT fk_checkins_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- ACHIEVEMENT/BADGE SYSTEM
-- =====================================================

CREATE TABLE IF NOT EXISTS achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT NULL,
    icon VARCHAR(100) NULL DEFAULT 'trophy',
    condition_type VARCHAR(50) NOT NULL COMMENT 'first_deposit, api_calls, referrals, checkin_streak, total_spent',
    condition_value INT UNSIGNED NOT NULL DEFAULT 1,
    reward_tokens DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_achievements_condition_type (condition_type),
    INDEX idx_achievements_is_active (is_active),
    INDEX idx_achievements_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_achievements (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    achievement_id INT UNSIGNED NOT NULL,
    unlocked_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_achievements_user_id (user_id),
    INDEX idx_user_achievements_achievement_id (achievement_id),
    UNIQUE KEY unique_user_achievement (user_id, achievement_id),
    
    CONSTRAINT fk_user_achievements_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_achievements_achievement_id 
        FOREIGN KEY (achievement_id) REFERENCES achievements(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample achievements
INSERT INTO achievements (name, description, icon, condition_type, condition_value, reward_tokens, sort_order) VALUES
('First Deposit', 'Nap tien lan dau tien', 'credit-card', 'first_deposit', 1, 10.00, 1),
('API Master', 'Goi 1000 API calls', 'code', 'api_calls', 1000, 50.00, 2),
('API Legend', 'Goi 10000 API calls', 'award', 'api_calls', 10000, 200.00, 3),
('Referral Champion', 'Gioi thieu 10 nguoi', 'users', 'referrals', 10, 100.00, 4),
('Streak King', 'Check-in 30 ngay lien tiep', 'calendar', 'checkin_streak', 30, 150.00, 5),
('Week Warrior', 'Check-in 7 ngay lien tiep', 'calendar', 'checkin_streak', 7, 30.00, 6),
('Big Spender', 'Nap tong 1 trieu VND', 'dollar-sign', 'total_spent', 1000000, 200.00, 7),
('Getting Started', 'Tao API key dau tien', 'key', 'first_key', 1, 5.00, 8);

-- =====================================================
-- ENHANCED TICKET SYSTEM
-- =====================================================

-- Support tickets with categories
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    subject VARCHAR(255) NOT NULL,
    category ENUM('billing', 'technical', 'account', 'other') NOT NULL DEFAULT 'other',
    priority ENUM('low', 'medium', 'high') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'resolved', 'closed') NOT NULL DEFAULT 'open',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_support_tickets_user_id (user_id),
    INDEX idx_support_tickets_status (status),
    INDEX idx_support_tickets_category (category),
    INDEX idx_support_tickets_priority (priority),
    INDEX idx_support_tickets_created_at (created_at),
    
    CONSTRAINT fk_support_tickets_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket messages
CREATE TABLE IF NOT EXISTS ticket_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NULL,
    admin_id INT UNSIGNED NULL,
    message TEXT NOT NULL,
    attachments JSON NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ticket_messages_ticket_id (ticket_id),
    INDEX idx_ticket_messages_user_id (user_id),
    INDEX idx_ticket_messages_admin_id (admin_id),
    
    CONSTRAINT fk_ticket_messages_ticket_id 
        FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ticket_messages_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_ticket_messages_admin_id 
        FOREIGN KEY (admin_id) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- NOTIFICATION CENTER
-- =====================================================

CREATE TABLE IF NOT EXISTS user_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL DEFAULT 'info' COMMENT 'info, success, warning, error, system',
    title VARCHAR(255) NOT NULL,
    message TEXT NULL,
    data JSON NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_user_notifications_user_id (user_id),
    INDEX idx_user_notifications_type (type),
    INDEX idx_user_notifications_is_read (is_read),
    INDEX idx_user_notifications_created_at (created_at),
    INDEX idx_user_notifications_user_read (user_id, is_read),
    
    CONSTRAINT fk_user_notifications_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- FAVORITES SYSTEM
-- =====================================================

CREATE TABLE IF NOT EXISTS user_favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    model_id VARCHAR(100) NOT NULL,
    sort_order INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_favorites_user_id (user_id),
    INDEX idx_favorites_model_id (model_id),
    INDEX idx_favorites_sort_order (sort_order),
    UNIQUE KEY unique_user_model (user_id, model_id),
    
    CONSTRAINT fk_favorites_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USAGE ALERTS SYSTEM
-- =====================================================

CREATE TABLE IF NOT EXISTS usage_alerts (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    alert_type ENUM('low_balance', 'daily_limit', 'monthly_limit') NOT NULL,
    threshold DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    is_enabled TINYINT(1) NOT NULL DEFAULT 1,
    notify_email TINYINT(1) NOT NULL DEFAULT 1,
    notify_telegram TINYINT(1) NOT NULL DEFAULT 0,
    last_triggered_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_usage_alerts_user_id (user_id),
    INDEX idx_usage_alerts_alert_type (alert_type),
    INDEX idx_usage_alerts_is_enabled (is_enabled),
    UNIQUE KEY unique_user_alert_type (user_id, alert_type),
    
    CONSTRAINT fk_usage_alerts_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- API KEY TEMPLATES
-- =====================================================

CREATE TABLE IF NOT EXISTS api_key_templates (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    settings JSON NOT NULL COMMENT 'rate_limit, allowed_models, usage_limit, etc',
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_templates_user_id (user_id),
    INDEX idx_templates_is_default (is_default),
    
    CONSTRAINT fk_templates_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
