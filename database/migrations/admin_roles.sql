-- Admin Roles Migration
-- Role-based access control for admin users

-- Create admin_roles table
CREATE TABLE IF NOT EXISTS admin_roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description VARCHAR(255) NULL,
    permissions JSON NOT NULL,
    is_system TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add role_id column to users table
ALTER TABLE users ADD COLUMN role_id INT NULL;
ALTER TABLE users ADD CONSTRAINT fk_users_role_id 
    FOREIGN KEY (role_id) REFERENCES admin_roles(id) 
    ON DELETE SET NULL;

-- Insert default roles
INSERT INTO admin_roles (name, description, permissions, is_system) VALUES
(
    'super_admin',
    'Full system access with all permissions',
    '["users.view","users.edit","users.ban","users.balance","deposits.view","deposits.approve","deposits.reject","tickets.view","tickets.reply","tickets.close","settings.view","settings.edit","plans.manage","providers.manage","coupons.manage","roles.manage","impersonate","health.view"]',
    1
),
(
    'admin',
    'Standard admin with most permissions',
    '["users.view","users.edit","users.ban","users.balance","deposits.view","deposits.approve","deposits.reject","tickets.view","tickets.reply","tickets.close","settings.view","plans.manage","providers.manage","coupons.manage","health.view"]',
    1
),
(
    'moderator',
    'Limited admin for user support and moderation',
    '["users.view","tickets.view","tickets.reply","tickets.close","deposits.view"]',
    1
);
