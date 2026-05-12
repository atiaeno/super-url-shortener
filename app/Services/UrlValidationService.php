<?php
// © Atia Hegazy — atiaeno.com

namespace App\Services;

class UrlValidationService
{
    private static array $blockedDomains = [
        'localhost',
        '127.0.0.1',
        '0.0.0.0',
        '::1',
    ];

    private static array $suspiciousPatterns = [
        '/javascript:/i',
        '/data:/i',
        '/vbscript:/i',
        '/file:/i',
        '/ftp:/i',
        '/<script/i',
        '/onload=/i',
        '/onerror=/i',
        '/onclick=/i',
    ];

    public static function isValidUrl(string $url): bool
    {
        // Basic URL validation
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        // Check for suspicious patterns
        foreach (self::$suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return false;
            }
        }

        // Parse URL and check components
        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            return false;
        }

        // Only allow HTTP and HTTPS
        if (!in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
            return false;
        }

        // Block localhost and private IPs
        if (self::isBlockedHost($parsed['host'])) {
            return false;
        }

        return true;
    }

    public static function sanitizeUrl(string $url): string
    {

        $url = trim($url);
        // Normalize URL
        $url = rtrim($url, '/');
        return $url;
    }

    private static function isBlockedHost(string $host): bool
    {
        // Check exact matches (localhost, loopback, etc.)
        if (in_array(strtolower($host), self::$blockedDomains)) {
            return true;
        }

        // Only apply IP-range checks when the host is actually an IP address
        if (filter_var($host, FILTER_VALIDATE_IP)) {
            // Block private and reserved IP ranges
            if (filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                return true;
            }
        }

        return false;
    }

    public static function getValidationError(string $url): ?string
    {
        if (empty($url)) {
            return 'URL is required.';
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return 'Invalid URL format.';
        }

        foreach (self::$suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $url)) {
                return 'URL contains suspicious content.';
            }
        }

        $parsed = parse_url($url);
        if (!$parsed || !isset($parsed['scheme'], $parsed['host'])) {
            return 'Invalid URL structure.';
        }

        if (!in_array(strtolower($parsed['scheme']), ['http', 'https'])) {
            return 'Only HTTP and HTTPS URLs are allowed.';
        }

        if (self::isBlockedHost($parsed['host'])) {
            return 'This URL is not allowed.';
        }

        return null;
    }
}
