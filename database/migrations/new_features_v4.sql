-- New Features v4 Migration
-- Enhanced Support Tickets System with Messages & Notification System Updates

-- =====================================================
-- ENHANCED SUPPORT TICKETS SYSTEM
-- =====================================================

-- Support Tickets (enhanced with categories, ticket numbers, and assignments)
CREATE TABLE IF NOT EXISTS support_tickets (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    ticket_number VARCHAR(20) UNIQUE NOT NULL COMMENT 'Format: TK-YYYYMMDD-XXX',
    subject VARCHAR(255) NOT NULL,
    category ENUM('billing', 'technical', 'account', 'api', 'other') DEFAULT 'other',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'waiting_reply', 'resolved', 'closed') DEFAULT 'open',
    assigned_to INT UNSIGNED DEFAULT NULL COMMENT 'Admin user_id assigned to this ticket',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    closed_at DATETIME DEFAULT NULL,
    
    INDEX idx_support_tickets_user_id (user_id),
    INDEX idx_support_tickets_ticket_number (ticket_number),
    INDEX idx_support_tickets_status (status),
    INDEX idx_support_tickets_category (category),
    INDEX idx_support_tickets_priority (priority),
    INDEX idx_support_tickets_assigned_to (assigned_to),
    INDEX idx_support_tickets_created_at (created_at DESC),
    
    CONSTRAINT fk_support_tickets_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_support_tickets_assigned_to 
        FOREIGN KEY (assigned_to) REFERENCES users(id) 
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ticket Messages (conversation thread)
CREATE TABLE IF NOT EXISTS ticket_messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT UNSIGNED NOT NULL,
    sender_type ENUM('user', 'admin') NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    message TEXT NOT NULL,
    attachments JSON DEFAULT NULL COMMENT 'Array of attachment file paths',
    is_internal TINYINT(1) DEFAULT 0 COMMENT 'Internal notes only visible to admins',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_ticket_messages_ticket_id (ticket_id),
    INDEX idx_ticket_messages_sender_type (sender_type),
    INDEX idx_ticket_messages_sender_id (sender_id),
    INDEX idx_ticket_messages_created_at (created_at),
    
    CONSTRAINT fk_ticket_messages_ticket_id 
        FOREIGN KEY (ticket_id) REFERENCES support_tickets(id) 
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_ticket_messages_sender_id 
        FOREIGN KEY (sender_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- USER NOTIFICATIONS (ensure table exists with proper structure)
-- =====================================================

-- Create user_notifications if it doesn't exist (may already be created in earlier migration)
CREATE TABLE IF NOT EXISTS user_notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    type VARCHAR(50) NOT NULL COMMENT 'info, warning, success, error, deposit_approved, ticket_reply, etc.',
    title VARCHAR(255) NOT NULL,
    message TEXT,
    data JSON DEFAULT NULL COMMENT 'Additional notification data',
    link VARCHAR(255) DEFAULT NULL COMMENT 'Link to related resource',
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    read_at DATETIME DEFAULT NULL,
    
    INDEX idx_user_notifications_user_read (user_id, is_read),
    INDEX idx_user_notifications_user_created (user_id, created_at DESC),
    INDEX idx_user_notifications_type (type),
    
    CONSTRAINT fk_user_notifications_user_id 
        FOREIGN KEY (user_id) REFERENCES users(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add columns to user_notifications if they don't exist
-- (Using stored procedure to handle ALTER TABLE safely)
DELIMITER //

CREATE PROCEDURE add_notification_columns()
BEGIN
    -- Add 'link' column if it doesn't exist
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'user_notifications' 
        AND COLUMN_NAME = 'link'
    ) THEN
        ALTER TABLE user_notifications ADD COLUMN link VARCHAR(255) DEFAULT NULL AFTER data;
    END IF;
    
    -- Add 'data' column if it doesn't exist
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'user_notifications' 
        AND COLUMN_NAME = 'data'
    ) THEN
        ALTER TABLE user_notifications ADD COLUMN data JSON DEFAULT NULL AFTER message;
    END IF;
    
    -- Add 'read_at' column if it doesn't exist
    IF NOT EXISTS (
        SELECT * FROM information_schema.COLUMNS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'user_notifications' 
        AND COLUMN_NAME = 'read_at'
    ) THEN
        ALTER TABLE user_notifications ADD COLUMN read_at DATETIME DEFAULT NULL AFTER is_read;
    END IF;
END //

DELIMITER ;

-- Execute the stored procedure and drop it
CALL add_notification_columns();
DROP PROCEDURE IF EXISTS add_notification_columns;

-- =====================================================
-- TICKET STATISTICS VIEW (for admin dashboard)
-- =====================================================

CREATE OR REPLACE VIEW v_ticket_stats AS
SELECT 
    COUNT(*) AS total_tickets,
    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open_tickets,
    SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress_tickets,
    SUM(CASE WHEN status = 'waiting_reply' THEN 1 ELSE 0 END) AS waiting_reply_tickets,
    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved_tickets,
    SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) AS closed_tickets,
    SUM(CASE WHEN priority = 'urgent' AND status NOT IN ('resolved', 'closed') THEN 1 ELSE 0 END) AS urgent_open_tickets
FROM support_tickets;

-- =====================================================
-- SAMPLE DATA (optional, comment out for production)
-- =====================================================

-- Insert sample categories for admin reference
-- Categories: billing, technical, account, api, other
-- Priorities: low, medium, high, urgent
-- Statuses: open, in_progress, waiting_reply, resolved, closed
