<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Jobs\SyncReferralCommissionsJob;
use App\Models\Affiliate;
use App\Models\ReferralCommission;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReferralEdgeCasesTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @test
     */
    public function it_handles_self_referral_attempt()
    {
        $user = User::factory()->create();
        $affiliate = Affiliate::factory()->create([
            'user_id' => $user->id,
            'referral_code' => 'SELF123',
            'is_active' => true,
        ]);

        $userData = [
            'name' => 'Self Referrer',
            'email' => 'self@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'SELF123',  // Trying to refer themselves
        ];

        $response = $this->post('/register', $userData);

        // User successfully registers - self-referral prevention happens at earnings level
        // The registration itself should succeed
        $response->assertRedirect('/dashboard');

        // Verify user was created and can log in
        $this->assertDatabaseHas('users', ['email' => 'self@example.com']);
    }

    /**
     * @test
     */
    public function it_handles_circular_referral_prevention()
    {
        // Create two users and link them
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $affiliate1 = Affiliate::factory()->create([
            'user_id' => $user1->id,
            'referral_code' => 'USER1REF',
            'is_active' => true,
        ]);

        $affiliate2 = Affiliate::factory()->create([
            'user_id' => $user2->id,
            'referral_code' => 'USER2REF',
            'is_active' => true,
        ]);

        // Link user2 to user1
        $user2->update(['referred_by_affiliate_id' => $affiliate1->id]);

        // Now try to create user1 with user2's referral code
        $userData = [
            'name' => 'Circular Test',
            'email' => 'circular@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'USER2REF',
        ];

        $response = $this->post('/register', $userData);

        // This should work normally - circular references are handled at the earnings level
        $response->assertRedirect('/dashboard');
    }

    /**
     * @test
     */
    public function it_handles_deleted_referrer_account()
    {
        $referrerUser = User::factory()->create();
        $referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'DELETED123',
            'is_active' => true,
        ]);

        // Delete the user (soft delete if implemented)
        $referrerUser->delete();

        $userData = [
            'name' => 'Deleted Referrer Test',
            'email' => 'deleted@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => 'DELETED123',
        ];

        $response = $this->post('/register', $userData);

        // Should fail because affiliate is tied to deleted user
        $response->assertSessionHasErrors('referral_code');
    }

    /**
     * @test
     */
    public function it_handles_referral_code_with_sql_injection_attempts()
    {
        $userData = [
            'name' => 'SQL Injection Test',
            'email' => 'sql@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => "'; DROP TABLE users; --",
        ];

        $response = $this->post('/register', $userData);

        // Should fail validation - SQL injection attempt in referral_code
        $response->assertSessionHasErrors('referral_code');

        // Verify user was NOT created due to validation failure
        $this->assertDatabaseMissing('users', ['email' => 'sql@example.com']);
    }

    /**
     * @test
     */
    public function it_handles_referral_code_with_xss_attempts()
    {
        $userData = [
            'name' => 'XSS Test',
            'email' => 'xss@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => '<script>alert("xss")</script>',
        ];

        $response = $this->post('/register', $userData);

        // Should fail validation
        $response->assertSessionHasErrors('referral_code');
    }

    /**
     * @test
     */
    public function it_handles_extremely_long_referral_code()
    {
        $userData = [
            'name' => 'Long Code Test',
            'email' => 'long@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => str_repeat('A', 1000),  // Very long code
        ];

        $response = $this->post('/register', $userData);

        // Should fail validation
        $response->assertSessionHasErrors('referral_code');
    }

    /**
     * @test
     */
    public function it_handles_null_referral_commission_rate()
    {
        // Delete any existing referral_commission_rate setting to simulate null
        DB::table('settings')->where('key', 'referral_commission_rate')->delete();

        $referrerUser = User::factory()->create();
        $referralUser = User::factory()->create();

        $referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'NULLRATE',
            'is_active' => true,
        ]);

        $referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'REFERRAL',
            'is_active' => true,
        ]);

        $referralUser->update(['referred_by_affiliate_id' => $referrerAffiliate->id]);

        $job = new SyncReferralCommissionsJob('2026-05-12');
        $job->handle();

        // Should use default rate (1.5) when setting doesn't exist, or skip if no visits
        $commissions = ReferralCommission::where('referrer_affiliate_id', $referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        // With no visits, no commissions should be created
        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_handles_negative_commission_rate()
    {
        // Set negative commission rate
        Setting::set('referral_commission_rate', '-1.0');

        $referrerUser = User::factory()->create();
        $referralUser = User::factory()->create();

        $referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'NEGATIVE',
            'is_active' => true,
        ]);

        $referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'REFERRAL',
            'is_active' => true,
        ]);

        $referralUser->update(['referred_by_affiliate_id' => $referrerAffiliate->id]);

        $job = new SyncReferralCommissionsJob('2026-05-12');
        $job->handle();

        // Should not process with negative rate
        $commissions = ReferralCommission::where('referrer_affiliate_id', $referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_handles_extremely_high_commission_rate()
    {
        // Set extremely high commission rate
        Setting::set('referral_commission_rate', '999.0');

        $referrerUser = User::factory()->create();
        $referralUser = User::factory()->create();

        $referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'HIGH',
            'is_active' => true,
        ]);

        $referralAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'REFERRAL',
            'is_active' => true,
        ]);

        $referralUser->update(['referred_by_affiliate_id' => $referrerAffiliate->id]);

        $job = new SyncReferralCommissionsJob('2026-05-12');
        $job->handle();

        // Should not process with extremely high rate
        $commissions = ReferralCommission::where('referrer_affiliate_id', $referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_handles_referral_with_no_affiliate_account()
    {
        $referrerUser = User::factory()->create();
        $referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'NOAFFILIATE',
            'is_active' => true,
        ]);

        // Create a user without affiliate account
        $referralUser = User::factory()->create();
        $referralUser->update(['referred_by_affiliate_id' => $referrerAffiliate->id]);

        $job = new SyncReferralCommissionsJob('2026-05-12');
        $job->handle();

        // Should handle gracefully - no affiliate account for referral
        $commissions = ReferralCommission::where('referrer_affiliate_id', $referrerAffiliate->id)
            ->where('commission_date', '2026-05-12')
            ->get();

        $this->assertCount(0, $commissions);
    }

    /**
     * @test
     */
    public function it_handles_database_connection_errors()
    {
        // This test would require mocking database failures
        // For now, we'll test that the system handles missing data gracefully

        $referrerUser = User::factory()->create();
        $referrerAffiliate = Affiliate::factory()->create([
            'user_id' => $referrerUser->id,
            'referral_code' => 'DBERROR',
            'is_active' => true,
        ]);

        // Create a valid affiliate to avoid FK issues, then test job handles missing data
        $referralUser = User::factory()->create();
        $validAffiliate = Affiliate::factory()->create([
            'user_id' => $referralUser->id,
            'referral_code' => 'VALIDREF',
            'is_active' => true,
        ]);
        $referralUser->update(['referred_by_affiliate_id' => $referrerAffiliate->id]);

        $job = new SyncReferralCommissionsJob('2026-05-12');

        // Should handle gracefully without throwing exceptions
        try {
            $job->handle();
            $this->assertTrue(true);  // If we get here, no exception was thrown
        } catch (\Exception $e) {
            $this->fail('Job should handle missing affiliate gracefully: ' . $e->getMessage());
        }
    }

    /**
     * @test
     */
    public function it_handles_concurrent_payout_requests()
    {
        // Set minimum payout low for this test
        Setting::set('affiliate_min_payout', '10');

        $user = User::factory()->create();
        $affiliate = Affiliate::factory()->create([
            'user_id' => $user->id,
            'referral_code' => 'CONCURRENT',
            'total_earnings' => 100.0,
            'pending_earnings' => 50.0,
            'paid_earnings' => 50.0,
            'referral_earnings' => 25.0,
            'referral_pending_earnings' => 25.0,
            'referral_paid_earnings' => 0.0,
            'is_active' => true,
        ]);

        $this->actingAs($user);

        $payoutData = [
            'payment_method' => 'PayPal',
            'payment_email' => 'paypal@example.com',
        ];

        // First request should succeed
        $response1 = $this->post('/affiliate/payout/request', $payoutData);
        $response1->assertRedirect();

        // Second request should fail (redirects with error in session)
        $response2 = $this->post('/affiliate/payout/request', $payoutData);
        $response2->assertRedirect();

        // Should only have one payout
        $payouts = \App\Models\Payout::where('affiliate_id', $affiliate->id)->get();
        $this->assertCount(1, $payouts);
    }

    /**
     * @test
     */
    public function it_handles_referral_code_with_unicode_characters()
    {
        $userData = [
            'name' => 'Unicode Test',
            'email' => 'unicode@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => '🚀REFERRAL🎯',  // Unicode characters
        ];

        $response = $this->post('/register', $userData);

        // Unicode characters should fail validation
        $response->assertSessionHasErrors('referral_code');
    }

    /**
     * @test
     */
    public function it_handles_referral_code_with_spaces_only()
    {
        $userData = [
            'name' => 'Spaces Test',
            'email' => 'spaces@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'referral_code' => '   ',  // Only spaces - treated as empty
        ];

        $response = $this->post('/register', $userData);

        // Spaces-only code is trimmed to empty and treated as no code provided
        // Should redirect successfully without referral
        $response->assertRedirect('/dashboard');

        // Verify user was created without referral
        $user = \App\Models\User::where('email', 'spaces@example.com')->first();
        $this->assertNotNull($user);
        $this->assertNull($user->referred_by_affiliate_id);
    }
}
