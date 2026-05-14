-- Changelog System Migration
-- Creates table for version updates and release notes

-- Changelogs table
CREATE TABLE IF NOT EXISTS changelogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    version VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type ENUM('feature', 'fix', 'improvement', 'security') NOT NULL DEFAULT 'feature',
    published_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_version (version),
    INDEX idx_type (type),
    INDEX idx_published_at (published_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert sample changelog entries
INSERT INTO changelogs (version, title, description, type, published_at) VALUES
('1.0.0', 'Initial Release', 'First public release of the Hay API Gateway platform.', 'feature', NOW()),
('1.0.0', 'User Authentication', 'Secure login and registration with email verification.', 'feature', NOW()),
('1.0.0', 'API Key Management', 'Create, rotate, and revoke API keys with usage tracking.', 'feature', NOW()),
('1.0.1', 'Fixed Login Issue', 'Resolved issue with session timeout on mobile devices.', 'fix', NOW()),
('1.0.1', 'Performance Optimization', 'Improved API response times by 40%.', 'improvement', NOW()),
('1.1.0', 'Multi-language Support', 'Added Vietnamese and English language support.', 'feature', NOW()),
('1.1.0', 'Enhanced Security', 'Implemented stronger password hashing and 2FA support.', 'security', NOW()),
('1.2.0', 'Referral System', 'Earn commissions by referring new users.', 'feature', NOW()),
('1.2.0', 'Webhook Notifications', 'Real-time notifications for API events.', 'feature', NOW());
