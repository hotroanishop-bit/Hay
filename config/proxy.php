<?php
/**
 * Proxy Configuration
 * Settings for upstream API provider, squid proxy, and model mapping
 * 
 * SECURITY: All internal details are hidden from end users
 * 
 * PRODUCTION: Set these environment variables:
 * - LOADIP_API_KEY: API key for LoadIP upstream provider
 * - PROXY_HOST: Squid proxy host
 * - PROXY_PORT: Squid proxy port
 * - PROXY_USERNAME: Squid proxy username
 * - PROXY_PASSWORD: Squid proxy password
 */

return [
    // Upstream provider configuration (LoadIP API)
    'upstream' => [
        'base_url' => 'https://api.loadip.com/v1',
        // Use environment variable in production, fallback to default for dev
        'api_key' => getenv('LOADIP_API_KEY') ?: 'sk-80c6f26e1d3336a7-5ahrqn-6975d32c',
    ],
    
    // Squid proxy configuration
    // Use environment variables in production for security
    'proxy' => [
        'host' => getenv('PROXY_HOST') ?: '103.157.204.171',
        'port' => (int)(getenv('PROXY_PORT') ?: 3128),
        'username' => getenv('PROXY_USERNAME') ?: 'morahub_admin',
        'password' => getenv('PROXY_PASSWORD') ?: 'M0r%40Hub%23Pr0xy%242026%21',
    ],
    
    // Model mapping: fake model name => real model name
    // Users only see fake names, backend maps to real names
    'model_mapping' => [
        'codex-5.4' => 'claude_sonet_4.5',
        'gpt-5-ultra' => 'minimax/MiniMax-M2.7',
    ],
    
    // Retry configuration
    'retry' => [
        'max_attempts' => 2,      // Total attempts (1 initial + 1 retry)
        'delay_ms' => 500,        // Delay between retries in milliseconds
    ],
    
    // cURL timeout settings
    'timeout' => [
        'connect' => 10,          // Connection timeout in seconds
        'request' => 120,         // Request timeout in seconds
        'streaming' => 300,       // Streaming request timeout in seconds
    ],
    
    // Retriable HTTP status codes (will trigger retry)
    'retriable_status_codes' => [
        500, // Internal Server Error
        502, // Bad Gateway
        503, // Service Unavailable
        504, // Gateway Timeout
        429, // Too Many Requests (rate limited)
    ],
];
