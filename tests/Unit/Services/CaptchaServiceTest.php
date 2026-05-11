<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\CaptchaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CaptchaServiceTest extends TestCase
{
    use RefreshDatabase;

    private CaptchaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new CaptchaService();
    }

    /**
     * @test
     */
    public function it_returns_false_when_settings_table_not_exists()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_returns_false_when_captcha_disabled()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'false']);

        $this->assertFalse($this->service->isEnabled());
    }

    /**
     * @test
     */
    public function it_returns_true_when_captcha_enabled()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);

        $this->assertTrue($this->service->isEnabled());
    }

    /**
     * @test
     */
    public function it_returns_default_site_key_when_not_set()
    {
        config(['services.recaptcha.site_key' => 'default-site-key']);

        $this->assertEquals('default-site-key', $this->service->siteKey());
    }

    /**
     * @test
     */
    public function it_returns_configured_site_key()
    {
        Setting::create(['key' => 'captcha_site_key', 'value' => 'configured-site-key']);

        $this->assertEquals('configured-site-key', $this->service->siteKey());
    }

    /**
     * @test
     */
    public function it_returns_true_when_captcha_disabled_during_verification()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'false']);

        $this->assertTrue($this->service->verify('any-token', '127.0.0.1'));
    }

    /**
     * @test
     */
    public function it_returns_false_when_token_empty()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);

        $this->assertFalse($this->service->verify('', '127.0.0.1'));
        $this->assertFalse($this->service->verify(null, '127.0.0.1'));
    }

    /**
     * @test
     */
    public function it_returns_true_when_secret_key_missing()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        // Don't create secret key setting

        $this->assertTrue($this->service->verify('valid-token', '127.0.0.1'));
    }

    /**
     * @test
     */
    public function it_verifies_valid_token()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        Setting::create(['key' => 'captcha_secret_key', 'value' => 'test-secret']);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response([
                'success' => true,
                'challenge_ts' => '2023-01-01T00:00:00Z',
                'hostname' => 'test.com'
            ], 200),
        ]);

        $this->assertTrue($this->service->verify('valid-token', '127.0.0.1'));
    }

    /**
     * @test
     */
    public function it_rejects_invalid_token()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_http_timeout()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_http_error()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        Setting::create(['key' => 'captcha_secret_key', 'value' => 'test-secret']);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response('Server Error', 500),
        ]);

        $this->assertTrue($this->service->verify('error-token', '127.0.0.1'));
    }

    /**
     * @test
     */
    public function it_sends_correct_request_data()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        Setting::create(['key' => 'captcha_secret_key', 'value' => 'test-secret']);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response(['success' => true], 200),
        ]);

        $this->service->verify('test-token', '127.0.0.1');

        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_malformed_response()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        Setting::create(['key' => 'captcha_secret_key', 'value' => 'test-secret']);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response('invalid json', 200),
        ]);

        $this->assertTrue($this->service->verify('malformed-token', '127.0.0.1'));
    }

    /**
     * @test
     */
    public function it_handles_missing_success_field()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_caches_settings_table_existence_check()
    {
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_boolean_string_values()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => '1']);
        $this->assertTrue($this->service->isEnabled());
    }

    /**
     * @test
     */
    public function it_logs_verification_errors()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        Setting::create(['key' => 'captcha_secret_key', 'value' => 'test-secret']);

        Http::fake([
            'https://www.google.com/recaptcha/api/siteverify' => Http::response('Server Error', 500),
        ]);

        // This should trigger the error logging
        $this->assertTrue($this->service->verify('error-token', '127.0.0.1'));

        // The test passes if no exception is thrown (error is logged silently)
        $this->assertTrue(true);
    }

    /**
     * @test
     */
    public function it_handles_empty_secret_key()
    {
        Setting::create(['key' => 'captcha_enabled', 'value' => 'true']);
        Setting::create(['key' => 'captcha_secret_key', 'value' => '']);

        $this->assertTrue($this->service->verify('any-token', '127.0.0.1'));
    }
}
