<?php
// © Atia Hegazy — atiaeno.com

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Mock CAPTCHA service for tests
        $this->mock(\App\Services\CaptchaService::class, function ($mock) {
            $mock->shouldReceive('isEnabled')->andReturn(false);
            $mock->shouldReceive('verify')->andReturn(true);
            $mock->shouldReceive('siteKey')->andReturn('test-key');
            $mock->shouldReceive('getProviderType')->andReturn('recaptcha');
        });
    }
}

trait CreatesApplication
{
    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }
}
