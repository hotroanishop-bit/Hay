<?php
/**
 * Token Helper Functions
 * Utility functions for token notation parsing
 */

/**
 * Parse token notation (e.g., 10k, 1M) to integer
 * 
 * Supported formats:
 * - '10k' or '10K' => 10000
 * - '100k' => 100000
 * - '1m' or '1M' => 1000000
 * - '10m' => 10000000
 * - Plain numbers pass through
 * 
 * @param mixed $input The input value to parse
 * @return int The parsed token count
 */
function parseTokenNotation($input): int
{
    // If already numeric, return as int
    if (is_numeric($input)) {
        return (int) $input;
    }
    
    // Must be a string at this point
    if (!is_string($input)) {
        return 0;
    }
    
    $input = trim($input);
    
    if ($input === '') {
        return 0;
    }
    
    // Check for K/k suffix (thousands)
    if (preg_match('/^(\d+(?:\.\d+)?)\s*[kK]$/i', $input, $matches)) {
        return (int) ((float) $matches[1] * 1000);
    }
    
    // Check for M/m suffix (millions)
    if (preg_match('/^(\d+(?:\.\d+)?)\s*[mM]$/i', $input, $matches)) {
        return (int) ((float) $matches[1] * 1000000);
    }
    
    // Check for B/b suffix (billions)
    if (preg_match('/^(\d+(?:\.\d+)?)\s*[bB]$/i', $input, $matches)) {
        return (int) ((float) $matches[1] * 1000000000);
    }
    
    // Fall back to plain numeric conversion
    if (is_numeric($input)) {
        return (int) $input;
    }
    
    return 0;
}

/**
 * Format token count to human-readable notation
 * 
 * @param int $tokens The token count
 * @return string Formatted string (e.g., "10K", "1.5M")
 */
function formatTokenNotation(int $tokens): string
{
    if ($tokens >= 1000000000) {
        $value = $tokens / 1000000000;
        return rtrim(rtrim(number_format($value, 1), '0'), '.') . 'B';
    }
    
    if ($tokens >= 1000000) {
        $value = $tokens / 1000000;
        return rtrim(rtrim(number_format($value, 1), '0'), '.') . 'M';
    }
    
    if ($tokens >= 1000) {
        $value = $tokens / 1000;
        return rtrim(rtrim(number_format($value, 1), '0'), '.') . 'K';
    }
    
    return (string) $tokens;
}
