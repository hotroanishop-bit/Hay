-- Migration: Professional UI Upgrade
-- New tables: themes, custom_pages, menu_items, notifications, user_plans
-- Modified tables: users (dual billing columns), plans (token quota columns)

-- -----------------------------------------------------
-- Table: themes
-- UI themes with CSS variables stored as JSON
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS themes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    css_variables JSON NOT NULL,
    is_default TINYINT(1) DEFAULT 0,
    created_by INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_themes_name (name),
    INDEX idx_themes_is_default (is_default),
    INDEX idx_themes_created_by (created_by),
    
    CONSTRAINT fk_themes_created_by 
        FOREIGN KEY (created_by) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: custom_pages
-- CMS-style custom pages with SEO support
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS custom_pages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    content TEXT NULL,
    meta_description VARCHAR(500) NULL,
    is_published TINYINT(1) DEFAULT 0,
    menu_order INT DEFAULT 0,
    show_in_menu TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_custom_pages_slug (slug),
    INDEX idx_custom_pages_is_published (is_published),
    INDEX idx_custom_pages_show_in_menu (show_in_menu),
    INDEX idx_custom_pages_menu_order (menu_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: menu_items
-- Dynamic navigation menu items for bottom nav, bottom sheet, etc.
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    label VARCHAR(100) NOT NULL,
    icon VARCHAR(100) NULL,
    url VARCHAR(500) NOT NULL,
    position VARCHAR(50) NOT NULL DEFAULT 'bottom_nav',
    parent_id INT UNSIGNED NULL,
    show_in_bottom_nav TINYINT(1) DEFAULT 0,
    show_in_bottom_sheet TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_menu_items_position (position),
    INDEX idx_menu_items_parent_id (parent_id),
    INDEX idx_menu_items_show_in_bottom_nav (show_in_bottom_nav),
    INDEX idx_menu_items_show_in_bottom_sheet (show_in_bottom_sheet),
    INDEX idx_menu_items_sort_order (sort_order),
    INDEX idx_menu_items_is_active (is_active),
    
    CONSTRAINT fk_menu_items_parent_id 
        FOREIGN KEY (parent_id) REFERENCES menu_items(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: notifications
-- User notifications (null user_id = broadcast to all)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_notifications_user_id (user_id),
    INDEX idx_notifications_type (type),
    INDEX idx_notifications_is_read (is_read),
    INDEX idx_notifications_created_at (created_at),
    INDEX idx_notifications_user_read (user_id, is_read),
    
    CONSTRAINT fk_notifications_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: user_plans
-- User plan subscriptions with token tracking
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS user_plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    plan_id INT UNSIGNED NOT NULL,
    tokens_remaining BIGINT DEFAULT 0,
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_user_plans_user_id (user_id),
    INDEX idx_user_plans_plan_id (plan_id),
    INDEX idx_user_plans_status (status),
    INDEX idx_user_plans_expires_at (expires_at),
    INDEX idx_user_plans_user_status (user_id, status),
    
    CONSTRAINT fk_user_plans_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_user_plans_plan_id 
        FOREIGN KEY (plan_id) REFERENCES plans(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Alter users table: Add dual billing support columns
-- Uses stored procedure pattern for safe column additions
-- -----------------------------------------------------

-- Add payg_balance column (Pay-As-You-Go balance)
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'payg_balance') = 0,
    'ALTER TABLE users ADD COLUMN payg_balance DECIMAL(15,2) DEFAULT 0.00 AFTER balance',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add current_plan_id column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'current_plan_id') = 0,
    'ALTER TABLE users ADD COLUMN current_plan_id INT UNSIGNED NULL AFTER payg_balance',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add plan_tokens column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'plan_tokens') = 0,
    'ALTER TABLE users ADD COLUMN plan_tokens BIGINT DEFAULT 0 AFTER current_plan_id',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add plan_tokens_expires_at column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'plan_tokens_expires_at') = 0,
    'ALTER TABLE users ADD COLUMN plan_tokens_expires_at TIMESTAMP NULL AFTER plan_tokens',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add daily_tokens_used column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'daily_tokens_used') = 0,
    'ALTER TABLE users ADD COLUMN daily_tokens_used INT DEFAULT 0 AFTER plan_tokens_expires_at',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add daily_tokens_reset_at column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'daily_tokens_reset_at') = 0,
    'ALTER TABLE users ADD COLUMN daily_tokens_reset_at DATE NULL AFTER daily_tokens_used',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add preferred_theme column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'preferred_theme') = 0,
    'ALTER TABLE users ADD COLUMN preferred_theme VARCHAR(50) DEFAULT ''light'' AFTER daily_tokens_reset_at',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add preferred_billing_type column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND COLUMN_NAME = 'preferred_billing_type') = 0,
    'ALTER TABLE users ADD COLUMN preferred_billing_type ENUM(''payg'', ''plan'') DEFAULT ''payg'' AFTER preferred_theme',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add index for current_plan_id if not exists
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'users' AND INDEX_NAME = 'idx_users_current_plan_id') = 0,
    'ALTER TABLE users ADD INDEX idx_users_current_plan_id (current_plan_id)',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------
-- Alter plans table: Add token quota and duration columns
-- -----------------------------------------------------

-- Add token_quota column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'token_quota') = 0,
    'ALTER TABLE plans ADD COLUMN token_quota BIGINT DEFAULT 0 AFTER price_multiplier',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add duration_days column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'duration_days') = 0,
    'ALTER TABLE plans ADD COLUMN duration_days INT DEFAULT 30 AFTER token_quota',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add is_free column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'is_free') = 0,
    'ALTER TABLE plans ADD COLUMN is_free TINYINT(1) DEFAULT 0 AFTER duration_days',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Add description column
SET @s = (SELECT IF(
    (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'plans' AND COLUMN_NAME = 'description') = 0,
    'ALTER TABLE plans ADD COLUMN description TEXT NULL AFTER is_free',
    'SELECT 1'
));
PREPARE stmt FROM @s;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- -----------------------------------------------------
-- Insert default themes
-- Light theme with standard CSS variables
-- -----------------------------------------------------
INSERT INTO themes (name, css_variables, is_default, created_by) VALUES
    ('light', JSON_OBJECT(
        '--bg-primary', '#ffffff',
        '--bg-secondary', '#f8fafc',
        '--bg-tertiary', '#f1f5f9',
        '--text-primary', '#1e293b',
        '--text-secondary', '#64748b',
        '--text-muted', '#94a3b8',
        '--border-color', '#e2e8f0',
        '--accent-primary', '#3b82f6',
        '--accent-secondary', '#8b5cf6',
        '--accent-success', '#22c55e',
        '--accent-warning', '#f59e0b',
        '--accent-danger', '#ef4444',
        '--shadow-sm', '0 1px 2px 0 rgb(0 0 0 / 0.05)',
        '--shadow-md', '0 4px 6px -1px rgb(0 0 0 / 0.1)',
        '--shadow-lg', '0 10px 15px -3px rgb(0 0 0 / 0.1)',
        '--radius-sm', '0.25rem',
        '--radius-md', '0.375rem',
        '--radius-lg', '0.5rem',
        '--card-bg', '#ffffff',
        '--nav-bg', '#ffffff',
        '--input-bg', '#ffffff'
    ), 1, NULL),
    ('dark', JSON_OBJECT(
        '--bg-primary', '#0f172a',
        '--bg-secondary', '#1e293b',
        '--bg-tertiary', '#334155',
        '--text-primary', '#f8fafc',
        '--text-secondary', '#cbd5e1',
        '--text-muted', '#94a3b8',
        '--border-color', '#334155',
        '--accent-primary', '#60a5fa',
        '--accent-secondary', '#a78bfa',
        '--accent-success', '#4ade80',
        '--accent-warning', '#fbbf24',
        '--accent-danger', '#f87171',
        '--shadow-sm', '0 1px 2px 0 rgb(0 0 0 / 0.3)',
        '--shadow-md', '0 4px 6px -1px rgb(0 0 0 / 0.4)',
        '--shadow-lg', '0 10px 15px -3px rgb(0 0 0 / 0.5)',
        '--radius-sm', '0.25rem',
        '--radius-md', '0.375rem',
        '--radius-lg', '0.5rem',
        '--card-bg', '#1e293b',
        '--nav-bg', '#1e293b',
        '--input-bg', '#0f172a'
    ), 0, NULL)
ON DUPLICATE KEY UPDATE css_variables = VALUES(css_variables);

-- -----------------------------------------------------
-- Insert default menu items for bottom navigation
-- -----------------------------------------------------
INSERT INTO menu_items (label, icon, url, position, parent_id, show_in_bottom_nav, show_in_bottom_sheet, sort_order, is_active) VALUES
    ('Home', 'home', '/dashboard', 'bottom_nav', NULL, 1, 0, 1, 1),
    ('API Keys', 'key', '/api-keys', 'bottom_nav', NULL, 1, 0, 2, 1),
    ('Billing', 'credit-card', '/billing', 'bottom_nav', NULL, 1, 0, 3, 1),
    ('Docs', 'book-open', '/docs', 'bottom_nav', NULL, 1, 0, 4, 1),
    ('More', 'menu', '#more', 'bottom_nav', NULL, 1, 1, 5, 1),
    -- Bottom sheet items (children of More)
    ('Profile', 'user', '/profile', 'bottom_sheet', NULL, 0, 1, 1, 1),
    ('Support', 'help-circle', '/tickets', 'bottom_sheet', NULL, 0, 1, 2, 1),
    ('Settings', 'settings', '/profile', 'bottom_sheet', NULL, 0, 1, 3, 1)
ON DUPLICATE KEY UPDATE label = VALUES(label);

-- -----------------------------------------------------
-- Update existing plans with token quota values
-- -----------------------------------------------------
UPDATE plans SET token_quota = 500000, duration_days = 30, is_free = 0, description = 'Great for individuals and small projects' WHERE name = 'Basic';
UPDATE plans SET token_quota = 2500000, duration_days = 30, is_free = 0, description = 'Perfect for growing teams and businesses' WHERE name = 'Pro';
UPDATE plans SET token_quota = 0, duration_days = 30, is_free = 0, description = 'Unlimited access for enterprise needs' WHERE name = 'Enterprise';

-- Insert a free plan if it does not exist
INSERT INTO plans (name, price_monthly, rate_limit_per_minute, daily_token_limit, price_multiplier, token_quota, duration_days, is_free, is_active, description) 
SELECT 'Free', 0.00, 10, 10000, 1.50, 0, 0, 1, 1, 'Free tier with limited daily tokens'
WHERE NOT EXISTS (SELECT 1 FROM plans WHERE name = 'Free');
