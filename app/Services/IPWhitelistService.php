<?php
/**
 * IP Whitelist Service
 * Handles IP address validation and whitelist matching
 * Supports IPv4 addresses and wildcard patterns
 */

class IPWhitelistService
{
    /**
     * Check if an IP address is allowed based on a whitelist
     * 
     * @param array|null $allowedIPs Array of allowed IP addresses/patterns, null means all allowed
     * @param string $requestIP The IP address to check
     * @return bool True if IP is allowed
     */
    public function isIPAllowed(?array $allowedIPs, string $requestIP): bool
    {
        // Null or empty array means all IPs are allowed
        if ($allowedIPs === null || empty($allowedIPs)) {
            return true;
        }

        // Normalize the request IP
        $requestIP = trim($requestIP);
        if (empty($requestIP)) {
            return false;
        }

        // Check each allowed IP/pattern
        foreach ($allowedIPs as $allowedIP) {
            $allowedIP = trim($allowedIP);
            if (empty($allowedIP)) {
                continue;
            }

            // Exact match
            if ($allowedIP === $requestIP) {
                return true;
            }

            // Wildcard match
            if (strpos($allowedIP, '*') !== false) {
                if ($this->matchWildcard($allowedIP, $requestIP)) {
                    return true;
                }
            }

            // CIDR notation match (e.g., 192.168.1.0/24)
            if (strpos($allowedIP, '/') !== false) {
                if ($this->matchCIDR($allowedIP, $requestIP)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Parse a newline or comma-separated IP list into an array
     * 
     * @param string $ipString The raw IP string input
     * @return array Array of IP addresses/patterns
     */
    public function parseIPList(string $ipString): array
    {
        if (empty(trim($ipString))) {
            return [];
        }

        // Split by newlines and commas
        $ips = preg_split('/[\r\n,]+/', $ipString);
        
        $result = [];
        foreach ($ips as $ip) {
            $ip = trim($ip);
            if (!empty($ip) && $this->validateIPPattern($ip)) {
                $result[] = $ip;
            }
        }

        return array_unique($result);
    }

    /**
     * Match an IP address against a wildcard pattern
     * Supports patterns like: 192.168.1.*, 192.168.*.*, 10.*.*.*
     * 
     * @param string $pattern The wildcard pattern
     * @param string $ip The IP address to match
     * @return bool True if IP matches the pattern
     */
    public function matchWildcard(string $pattern, string $ip): bool
    {
        // Convert wildcard pattern to regex
        $regex = '/^' . str_replace(
            ['\\*', '\\.'],
            ['[0-9]+', '\\.'],
            preg_quote($pattern, '/')
        ) . '$/';

        // Adjust regex to properly handle wildcards
        $regex = str_replace('[0-9]+', '\\d+', $regex);

        return (bool) preg_match($regex, $ip);
    }

    /**
     * Match an IP address against a CIDR notation range
     * 
     * @param string $cidr CIDR notation (e.g., 192.168.1.0/24)
     * @param string $ip The IP address to match
     * @return bool True if IP is within the CIDR range
     */
    public function matchCIDR(string $cidr, string $ip): bool
    {
        if (strpos($cidr, '/') === false) {
            return false;
        }

        list($subnet, $mask) = explode('/', $cidr);
        
        // Validate IP and subnet
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
            !filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        $mask = (int) $mask;
        if ($mask < 0 || $mask > 32) {
            return false;
        }

        $ipLong = ip2long($ip);
        $subnetLong = ip2long($subnet);
        $maskLong = -1 << (32 - $mask);

        return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
    }

    /**
     * Validate an IP address or pattern format
     * 
     * @param string $ip The IP address or pattern to validate
     * @return bool True if valid format
     */
    public function validateIP(string $ip): bool
    {
        $ip = trim($ip);
        
        // Check standard IPv4
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return true;
        }

        // Check standard IPv6
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            return true;
        }

        return false;
    }

    /**
     * Validate an IP pattern (including wildcards and CIDR)
     * 
     * @param string $pattern The pattern to validate
     * @return bool True if valid pattern
     */
    public function validateIPPattern(string $pattern): bool
    {
        $pattern = trim($pattern);
        
        if (empty($pattern)) {
            return false;
        }

        // Standard IP address
        if ($this->validateIP($pattern)) {
            return true;
        }

        // Wildcard pattern (e.g., 192.168.1.*, 10.*.*.*)
        if (strpos($pattern, '*') !== false) {
            // Replace * with a valid octet for validation
            $testPattern = str_replace('*', '0', $pattern);
            return (bool) filter_var($testPattern, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4);
        }

        // CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($pattern, '/') !== false) {
            list($subnet, $mask) = explode('/', $pattern);
            if (!filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
                return false;
            }
            $mask = (int) $mask;
            return $mask >= 0 && $mask <= 32;
        }

        return false;
    }

    /**
     * Format IP list for display
     * 
     * @param array|null $ips Array of IPs or null
     * @return string Formatted string for display
     */
    public function formatIPList(?array $ips): string
    {
        if ($ips === null || empty($ips)) {
            return 'All IPs allowed';
        }

        return implode(', ', $ips);
    }
}
