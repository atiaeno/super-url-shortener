<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Models\Affiliate;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReferralRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected $referrerAffiliate;
    protected $inactiveAffiliate;

    protected function setUp(): void
    {
        parent::setUp();

        // Disable CAPTCHA for testing
        config(['captcha.enabled' => false]);
        config(['captcha.register' => false]);

        // Create a referrer affiliate
        $referrerUser = User::factory()->create();
        $this->referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'REFERRER123',
            'is_active' => true,
        ]);

        // Create inactive affiliate for testing
        $inactiveUser = User::factory()->create();
        $this->inactiveAffiliate = Affiliate::factory()->create([
            'user_id' => $inactiveUser->id,
            'referral_code' => 'INACTIVE123',
            'is_active' => false,
        ]);
    }

    /**
     * @test
     */
    public function it_can_register_with_valid_referral_code()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'REFERRER123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'john@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($this->referrerAffiliate->id, $user->referred_by_affiliate_id);
        $this->assertTrue(Hash::check('password123', $user->password));
    }

    /**
     * @test
     */
    public function it_can_register_without_referral_code()
    {
        $userData = [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'jane@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->referred_by_affiliate_id);
    }

    /**
     * @test
     */
    public function it_rejects_invalid_referral_code()
    {
        $userData = [
            'name' => 'Invalid User',
            'email' => 'invalid@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'INVALID123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('referral_code');

        $user = User::where('email', 'invalid@example.com')->first();
        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function it_rejects_inactive_referral_code()
    {
        $userData = [
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'INACTIVE123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('referral_code');

        $user = User::where('email', 'inactive@example.com')->first();
        $this->assertNull($user);
    }

    /**
     * @test
     */
    public function it_handles_referral_code_case_insensitively()
    {
        $userData = [
            'name' => 'Case User',
            'email' => 'case@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'referrer123',  // lowercase
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'case@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($this->referrerAffiliate->id, $user->referred_by_affiliate_id);
    }

    /**
     * @test
     */
    public function it_trims_whitespace_from_referral_code()
    {
        $userData = [
            'name' => 'Trim User',
            'email' => 'trim@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => '  REFERRER123  ',  // with whitespace
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'trim@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($this->referrerAffiliate->id, $user->referred_by_affiliate_id);
    }

    /**
     * @test
     */
    public function it_prevents_duplicate_email_with_referral_code()
    {
        // First registration
        User::factory()->create([
            'email' => 'duplicate@example.com',
        ]);

        $userData = [
            'name' => 'Duplicate User',
            'email' => 'duplicate@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'REFERRER123',
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('email');

        $users = User::where('email', 'duplicate@example.com')->get();
        $this->assertCount(1, $users);
    }

    /**
     * @test
     */
    public function it_stores_referral_relationship_after_successful_registration()
    {
        $userData = [
            'name' => 'Success User',
            'email' => 'success@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'REFERRER123',
        ];

        $this->post('/register', $userData);

        $user = User::where('email', 'success@example.com')->first();
        $this->assertEquals($this->referrerAffiliate->id, $user->referred_by_affiliate_id);

        // Verify the relationship works
        $referredBy = $user->referredBy;
        $this->assertInstanceOf(Affiliate::class, $referredBy);
        $this->assertEquals('REFERRER123', $referredBy->referral_code);
    }

    /**
     * @test
     */
    public function it_handles_empty_referral_code_gracefully()
    {
        $userData = [
            'name' => 'Empty User',
            'email' => 'empty@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => '',
        ];

        $response = $this->post('/register', $userData);

        $response->assertRedirect('/dashboard');

        $user = User::where('email', 'empty@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->referred_by_affiliate_id);
    }

    /**
     * @test
     */
    public function registration_page_loads_with_referral_code_parameter()
    {
        // This tests that the registration page can handle URL parameters
        // The actual referral code processing happens in the POST request
        $response = $this->get('/register?ref=REFERRER123');

        $response->assertStatus(200);
    }

    /**
     * @test
     */
    public function it_validates_referral_code_length()
    {
        $userData = [
            'name' => 'Long User',
            'email' => 'long@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => str_repeat('A', 51),  // 51 characters, exceeds max 50
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('referral_code');
    }

    /**
     * @test
     */
    public function it_handles_referral_code_with_special_characters()
    {
        $userData = [
            'name' => 'Special User',
            'email' => 'special@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'REFERRER-123_!@#',  // special characters
        ];

        $response = $this->post('/register', $userData);

        $response->assertSessionHasErrors('referral_code');

        $user = User::where('email', 'special@example.com')->first();
        $this->assertNull($user);
    }
}
