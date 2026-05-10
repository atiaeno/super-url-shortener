<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Feature;

use App\Models\Payout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPayoutTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_admin_can_view_payout_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/payouts');

        $response->assertStatus(200);
    }

    public function test_admin_can_filter_payouts_by_status(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/payouts?status=pending');

        $response->assertStatus(200);
    }

    public function test_non_admin_cannot_access_payouts(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/payouts');

        $response->assertStatus(403);
    }

    public function test_admin_can_approve_payout(): void
    {
        $payout = Payout::factory()->create(['status' => Payout::STATUS_PENDING]);

        $response = $this->actingAs($this->admin)->post("/admin/payouts/{$payout->id}/approve", [
            'note' => 'Approved for payment',
        ]);

        $response->assertRedirect();
        $this->assertEquals(Payout::STATUS_APPROVED, $payout->fresh()->status);
    }

    public function test_admin_can_reject_payout(): void
    {
        $payout = Payout::factory()->create(['status' => Payout::STATUS_PENDING]);

        $response = $this->actingAs($this->admin)->post("/admin/payouts/{$payout->id}/reject", [
            'note' => 'Insufficient balance',
        ]);

        $response->assertRedirect();
        $this->assertEquals(Payout::STATUS_REJECTED, $payout->fresh()->status);
    }

    public function test_admin_can_mark_payout_as_paid(): void
    {
        $payout = Payout::factory()->create(['status' => Payout::STATUS_APPROVED]);

        $response = $this->actingAs($this->admin)->post("/admin/payouts/{$payout->id}/mark-paid");

        $response->assertRedirect();
        $this->assertEquals(Payout::STATUS_PAID, $payout->fresh()->status);
    }
}
