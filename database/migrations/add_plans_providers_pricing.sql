-- Migration: Add Plans, Providers, and Model Pricing tables
-- For API Proxy Gateway billing and configuration

-- -----------------------------------------------------
-- Table: plans
-- Subscription plans with rate limits and pricing
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS plans (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    price_monthly DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    rate_limit_per_minute INT UNSIGNED NOT NULL DEFAULT 60,
    daily_token_limit INT UNSIGNED NULL,
    price_multiplier DECIMAL(5, 2) NOT NULL DEFAULT 1.00,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_plans_name (name),
    INDEX idx_plans_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: providers
-- AI API providers (OpenAI, Anthropic, etc.)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS providers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    base_url VARCHAR(500) NOT NULL,
    api_key_encrypted TEXT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_providers_name (name),
    INDEX idx_providers_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Table: model_pricing
-- Pricing per model per provider
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS model_pricing (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    provider_id INT UNSIGNED NOT NULL,
    model_name VARCHAR(100) NOT NULL,
    input_price_per_1k DECIMAL(10, 6) NOT NULL DEFAULT 0.000000,
    output_price_per_1k DECIMAL(10, 6) NOT NULL DEFAULT 0.000000,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_model_pricing_provider_id (provider_id),
    INDEX idx_model_pricing_model_name (model_name),
    INDEX idx_model_pricing_is_active (is_active),
    UNIQUE INDEX idx_model_pricing_unique (provider_id, model_name),
    
    CONSTRAINT fk_model_pricing_provider_id 
        FOREIGN KEY (provider_id) REFERENCES providers(id) 
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Alter users table: Add plan_id column
-- -----------------------------------------------------
ALTER TABLE users 
    ADD COLUMN plan_id INT UNSIGNED NULL AFTER is_admin,
    ADD INDEX idx_users_plan_id (plan_id),
    ADD CONSTRAINT fk_users_plan_id 
        FOREIGN KEY (plan_id) REFERENCES plans(id) 
        ON DELETE SET NULL ON UPDATE CASCADE;

-- -----------------------------------------------------
-- Seed data: Plans
-- -----------------------------------------------------
INSERT INTO plans (name, price_monthly, rate_limit_per_minute, daily_token_limit, price_multiplier, is_active) VALUES
    ('Basic', 9.99, 60, 100000, 1.20, 1),
    ('Pro', 29.99, 200, 500000, 1.00, 1),
    ('Enterprise', 99.99, 1000, NULL, 0.80, 1);

-- -----------------------------------------------------
-- Seed data: Providers
-- -----------------------------------------------------
INSERT INTO providers (name, base_url, api_key_encrypted, is_active) VALUES
    ('OpenAI', 'https://api.openai.com/v1', NULL, 1),
    ('Anthropic', 'https://api.anthropic.com/v1', NULL, 1);

-- -----------------------------------------------------
-- Seed data: Model Pricing
-- Prices are per 1,000 tokens (input/output)
-- IMPORTANT: Use FAKE model names that users see (not real upstream model names)
-- These must match the model_mapping keys in config/proxy.php
-- -----------------------------------------------------
INSERT INTO model_pricing (provider_id, model_name, input_price_per_1k, output_price_per_1k, is_active) VALUES
    -- Fake model names exposed to users (for billing calculations)
    ((SELECT id FROM providers WHERE name = 'OpenAI'), 'codex-5.4', 0.015000, 0.075000, 1),
    ((SELECT id FROM providers WHERE name = 'OpenAI'), 'gpt-5-ultra', 0.008000, 0.024000, 1);

-- -----------------------------------------------------
-- Alter usage_logs table: Add detailed tracking columns
-- -----------------------------------------------------
ALTER TABLE usage_logs 
    ADD COLUMN input_tokens INT UNSIGNED DEFAULT 0 AFTER tokens_used,
    ADD COLUMN output_tokens INT UNSIGNED DEFAULT 0 AFTER input_tokens,
    ADD COLUMN model VARCHAR(100) NULL AFTER output_tokens,
    ADD COLUMN response_time_ms INT UNSIGNED DEFAULT 0 AFTER model,
    ADD COLUMN request_id VARCHAR(36) NULL AFTER response_time_ms;

-- Add indexes for efficient querying
ALTER TABLE usage_logs
    ADD INDEX idx_usage_logs_model (model),
    ADD INDEX idx_usage_logs_response_time_ms (response_time_ms),
    ADD INDEX idx_usage_logs_request_id (request_id);
