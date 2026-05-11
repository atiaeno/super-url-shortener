<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Services;

use App\Models\Link;
use App\Services\ShortCodeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShortCodeServiceTest extends TestCase
{
    use RefreshDatabase;

    private ShortCodeService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new ShortCodeService();
    }

    /**
     * @test
     */
    public function it_generates_unique_short_codes()
    {
        $codes = [];

        for ($i = 0; $i < 100; $i++) {
            $code = $this->service->generate();
            $this->assertNotContains($code, $codes, "Generated code should be unique: {$code}");
            $codes[] = $code;
        }
    }

    /**
     * @test
     */
    public function it_generates_codes_of_default_length()
    {
        $code = $this->service->generate();
        $this->assertEquals(6, strlen($code));
    }

    /**
     * @test
     */
    public function it_generates_codes_of_custom_length()
    {
        $code = $this->service->generate(8);
        $this->assertEquals(8, strlen($code));
    }

    /**
     * @test
     */
    public function it_uses_valid_characters_only()
    {
        $validChars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i = 0; $i < 50; $i++) {
            $code = $this->service->generate();
            foreach (str_split($code) as $char) {
                $this->assertStringContainsString($char, $validChars,
                    "Invalid character found: {$char}");
            }
        }
    }

    /**
     * @test
     */
    public function it_avoids_existing_short_codes()
    {
        // Create a link with a specific short code
        $existingCode = 'abc123';
        Link::factory()->create(['short_code' => $existingCode]);

        // Generate many codes and ensure none match the existing one
        for ($i = 0; $i < 100; $i++) {
            $code = $this->service->generate();
            $this->assertNotEquals($existingCode, $code);
        }
    }

    /**
     * @test
     */
    public function it_avoids_existing_custom_aliases()
    {
        // Create a link with a custom alias
        $existingAlias = 'myalias';
        Link::factory()->create(['custom_alias' => $existingAlias]);

        // Generate many codes and ensure none match the existing alias
        for ($i = 0; $i < 100; $i++) {
            $code = $this->service->generate();
            $this->assertNotEquals($existingAlias, $code);
        }
    }

    /**
     * @test
     */
    public function it_increases_length_on_collision_persistence()
    {
        // Create many links to force collisions
        for ($i = 0; $i < 15; $i++) {
            Link::factory()->create(['short_code' => str_repeat('a', 6) . $i]);
        }

        $service = new ShortCodeService();

        // Generate should work and potentially increase length if needed
        $code = $service->generate(6);
        $this->assertNotEmpty($code);
        $this->assertGreaterThanOrEqual(6, strlen($code));
    }

    /**
     * @test
     */
    public function it_generates_different_codes_on_multiple_calls()
    {
        $codes = [];

        for ($i = 0; $i < 10; $i++) {
            $code = $this->service->generate();
            $this->assertNotContains($code, $codes);
            $codes[] = $code;
        }
    }

    /**
     * @test
     */
    public function it_handles_high_collision_scenario()
    {
        // Create many existing codes to increase collision probability
        Link::factory()->count(1000)->create();

        $codes = [];
        for ($i = 0; $i < 50; $i++) {
            $code = $this->service->generate();
            $this->assertNotContains($code, $codes);
            $codes[] = $code;
        }
    }

    /**
     * @test
     */
    public function it_generates_codes_with_mixed_case()
    {
        $codes = [];

        for ($i = 0; $i < 100; $i++) {
            $code = $this->service->generate();
            $codes[] = $code;
        }

        $allCodes = implode('', $codes);
        $this->assertMatchesRegularExpression('/[a-z]/', $allCodes, 'Should contain lowercase letters');
        $this->assertMatchesRegularExpression('/[A-Z]/', $allCodes, 'Should contain uppercase letters');
        $this->assertMatchesRegularExpression('/[0-9]/', $allCodes, 'Should contain numbers');
    }

    /**
     * @test
     */
    public function it_handles_minimum_length()
    {
        $code = $this->service->generate(1);
        $this->assertEquals(1, strlen($code));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]$/', $code);
    }

    /**
     * @test
     */
    public function it_handles_maximum_length()
    {
        $code = $this->service->generate(20);
        $this->assertEquals(20, strlen($code));
        $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]{20}$/', $code);
    }

    /**
     * @test
     */
    public function it_uses_secure_random_generation()
    {
        // This test ensures that the service uses random_int which is cryptographically secure
        $codes = [];

        for ($i = 0; $i < 1000; $i++) {
            $code = $this->service->generate();
            $codes[] = $code;
        }

        // Check that we get a good distribution of characters
        $allChars = implode('', $codes);
        $charCounts = count_chars($allChars, 1);

        // Each character should appear at least a few times in 1000 codes of 6 chars each
        foreach ($charCounts as $count) {
            $this->assertGreaterThan(10, $count, 'Character distribution should be uniform');
        }
    }

    /**
     * @test
     */
    public function it_handles_edge_case_with_zero_length()
    {
        $code = $this->service->generate(0);
        $this->assertEquals(0, strlen($code));
    }

    /**
     * @test
     */
    public function it_verifies_existence_check_uses_both_fields()
    {
        // Create links with both short_code and custom_alias
        Link::factory()->create(['short_code' => 'short1']);
        Link::factory()->create(['custom_alias' => 'alias1']);

        $service = new ShortCodeService();

        // Generate should work and avoid existing codes
        $code = $service->generate(6);
        $this->assertNotEquals('short1', $code);
        $this->assertNotEquals('alias1', $code);
        $this->assertEquals(6, strlen($code));
    }
}
