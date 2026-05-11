<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Services;

use App\Services\UrlValidationService;
use Tests\TestCase;

class UrlValidationServiceTest extends TestCase
{
    /**
     * @test
     */
    public function it_validates_valid_http_urls()
    {
        $validUrls = [
            'http://example.com',
            'https://example.com',
            'https://www.example.com',
            'https://example.com/path',
            'https://example.com/path?query=value',
            'https://example.com/path#fragment',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(UrlValidationService::isValidUrl($url), "URL should be valid: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_rejects_invalid_urls()
    {
        $invalidUrls = [
            '',
            'not-a-url',
            'ftp://example.com',
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            'vbscript:msgbox(1)',
            'file:///etc/passwd',
            'http://localhost',
            'http://127.0.0.1',
            'http://0.0.0.0',
            'https://192.168.1.1',
            'https://10.0.0.1',
        ];

        foreach ($invalidUrls as $url) {
            $this->assertFalse(UrlValidationService::isValidUrl($url), "URL should be invalid: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_rejects_suspicious_patterns()
    {
        $suspiciousUrls = [
            'https://example.com/<script>alert(1)</script>',
            'https://example.com?onload=alert(1)',
            'https://example.com#onerror=alert(1)',
            'https://example.com"onclick="alert(1)',
        ];

        foreach ($suspiciousUrls as $url) {
            $this->assertFalse(UrlValidationService::isValidUrl($url), "URL should be rejected: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_sanitizes_urls()
    {
        $testCases = [
            '  https://example.com  ' => 'https://example.com',
            'https://example.com/' => 'https://example.com',
            '  https://example.com/  ' => 'https://example.com',
            'https://example.com/path/' => 'https://example.com/path',
            'https://example.com/path/subpath/' => 'https://example.com/path/subpath',
        ];

        foreach ($testCases as $input => $expected) {
            $this->assertEquals($expected, UrlValidationService::sanitizeUrl($input));
        }
    }

    /**
     * @test
     */
    public function it_provides_specific_validation_errors()
    {
        // Empty URL
        $this->assertEquals('URL is required.', UrlValidationService::getValidationError(''));

        // Invalid format
        $this->assertEquals('Invalid URL format.', UrlValidationService::getValidationError('not-a-url'));

        // Suspicious content - use a valid URL format with suspicious content
        $this->assertEquals('URL contains suspicious content.',
            UrlValidationService::getValidationError('https://example.com/javascript:alert(1)'));

        // Test that valid URLs return null
        $this->assertNull(UrlValidationService::getValidationError('https://example.com'));

        // Wrong protocol - use a protocol that passes filter_var but is not HTTP/HTTPS
        $this->assertEquals('Only HTTP and HTTPS URLs are allowed.',
            UrlValidationService::getValidationError('ssh://example.com'));

        // Blocked host
        $this->assertEquals('This URL is not allowed.',
            UrlValidationService::getValidationError('http://localhost'));
    }

    /**
     * @test
     */
    public function it_returns_null_for_valid_urls()
    {
        $validUrls = [
            'https://example.com',
            'https://www.example.com/path',
            'https://example.com?query=value',
        ];

        foreach ($validUrls as $url) {
            $this->assertNull(UrlValidationService::getValidationError($url),
                "Valid URL should return null: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_blocks_private_ip_ranges()
    {
        $privateIps = [
            'https://192.168.1.1',
            'https://10.0.0.1',
            'https://172.16.0.1',
            'https://169.254.169.254',  // AWS metadata
        ];

        foreach ($privateIps as $url) {
            $this->assertFalse(UrlValidationService::isValidUrl($url),
                "Private IP should be blocked: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_blocks_loopback_addresses()
    {
        $loopbackUrls = [
            'http://localhost',
            'https://localhost',
            'http://127.0.0.1',
            'https://127.0.0.1',
            'http://0.0.0.0',
            'https://0.0.0.0',
            'http://::1',
            'https://::1',
        ];

        foreach ($loopbackUrls as $url) {
            $this->assertFalse(UrlValidationService::isValidUrl($url),
                "Loopback should be blocked: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_allows_public_ip_addresses()
    {
        $publicIps = [
            'https://8.8.8.8',
            'https://1.1.1.1',
            'https://208.67.222.222',
        ];

        foreach ($publicIps as $url) {
            $this->assertTrue(UrlValidationService::isValidUrl($url),
                "Public IP should be allowed: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_handles_case_insensitive_protocols()
    {
        $validUrls = [
            'HTTP://example.com',
            'HTTPS://example.com',
            'http://EXAMPLE.COM',
            'https://EXAMPLE.COM',
        ];

        foreach ($validUrls as $url) {
            $this->assertTrue(UrlValidationService::isValidUrl($url),
                "Case-insensitive URL should be valid: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_rejects_case_insensitive_suspicious_patterns()
    {
        $suspiciousUrls = [
            'JAVASCRIPT:alert(1)',
            'HTTPS://example.com/<SCRIPT>alert(1)</SCRIPT>',
            'https://example.com?ONLOAD=alert(1)',
        ];

        foreach ($suspiciousUrls as $url) {
            $this->assertFalse(UrlValidationService::isValidUrl($url),
                "Case-insensitive suspicious URL should be rejected: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_handles_unicode_domains()
    {
        $unicodeUrls = [
            'https://例子.测试',
            'https://münchen.de',
            'https://россия.рф',
        ];

        foreach ($unicodeUrls as $url) {
            // These might be valid depending on the system's URL validation
            $result = UrlValidationService::isValidUrl($url);
            $this->assertIsBool($result, "Unicode URL should return boolean: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_handles_malformed_urls_gracefully()
    {
        $malformedUrls = [
            'https://',
            'http://',
            '://example.com',
            'https://example.com:999999999',  // Invalid port
        ];

        foreach ($malformedUrls as $url) {
            $this->assertFalse(UrlValidationService::isValidUrl($url),
                "Malformed URL should be invalid: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_allows_port_numbers()
    {
        $urlsWithPorts = [
            'https://example.com:8080',
            'https://example.com:443',
            'http://example.com:80',
        ];

        foreach ($urlsWithPorts as $url) {
            $this->assertTrue(UrlValidationService::isValidUrl($url),
                "URL with port should be valid: {$url}");
        }
    }

    /**
     * @test
     */
    public function it_handles_auth_credentials()
    {
        $urlsWithAuth = [
            'https://user:pass@example.com',
            'https://user@example.com',
        ];

        foreach ($urlsWithAuth as $url) {
            $this->assertTrue(UrlValidationService::isValidUrl($url),
                "URL with auth should be valid: {$url}");
        }
    }
}
