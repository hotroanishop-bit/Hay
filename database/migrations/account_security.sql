-- Account Security Migration
-- Adds failed login tracking and account lockout to users table

ALTER TABLE users
ADD COLUMN IF NOT EXISTS failed_login_attempts INT NOT NULL DEFAULT 0,
ADD COLUMN IF NOT EXISTS locked_until TIMESTAMP NULL DEFAULT NULL;

-- Index for locked_until queries
CREATE INDEX IF NOT EXISTS idx_users_locked_until ON users(locked_until);
