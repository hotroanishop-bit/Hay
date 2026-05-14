-- API Key Security Migration
-- Adds model scope restrictions and IP whitelisting to API keys
-- Run after schema.sql

-- Add allowed_models column (JSON array of allowed model names, NULL = all allowed)
ALTER TABLE api_keys 
ADD COLUMN allowed_models JSON DEFAULT NULL 
COMMENT 'JSON array of allowed model names. NULL means all models allowed.'
AFTER model;

-- Add allowed_ips column (JSON array of allowed IP addresses/patterns, NULL = all allowed)
ALTER TABLE api_keys 
ADD COLUMN allowed_ips JSON DEFAULT NULL 
COMMENT 'JSON array of allowed IP addresses/patterns (supports wildcards like 192.168.*). NULL means all IPs allowed.'
AFTER allowed_models;

-- Example values:
-- allowed_models: ["codex-5.4", "gpt-5-ultra"] or NULL for all
-- allowed_ips: ["192.168.1.100", "10.0.0.*", "2001:db8::1"] or NULL for all
