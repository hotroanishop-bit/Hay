-- Migration: Add preferred_language column to users table
-- Date: 2024
-- Description: Adds preferred_language column to store user's language preference

-- Add preferred_language column to users table
-- Default is 'en' for English, supports 'vi' for Vietnamese
ALTER TABLE users 
ADD COLUMN IF NOT EXISTS preferred_language VARCHAR(5) DEFAULT 'en' AFTER email;

-- Add index for potential filtering/querying by language
CREATE INDEX IF NOT EXISTS idx_users_preferred_language ON users(preferred_language);

-- Update existing users to have English as default (if column already exists but has NULL)
UPDATE users SET preferred_language = 'en' WHERE preferred_language IS NULL;
