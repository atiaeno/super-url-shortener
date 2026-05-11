<?php
// © Atia Hegazy — atiaeno.com

namespace Tests\Unit\Models;

use App\Models\Affiliate;
use App\Models\Link;
use App\Models\Report;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_many_links()
    {
        $user = User::factory()->create();
        
        $links = Link::factory()->count(3)->create(['user_id' => $user->id]);

        $this->assertCount(3, $user->links);
        $this->assertEquals($links->pluck('id'), $user->links->pluck('id'));
    }

    /** @test */
    public function it_has_one_affiliate()
    {
        $user = User::factory()->create();
        $affiliate = Affiliate::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(Affiliate::class, $user->affiliate);
        $this->assertEquals($affiliate->id, $user->affiliate->id);
    }

    /** @test */
    public function it_can_have_no_affiliate()
    {
        $user = User::factory()->create();

        $this->assertNull($user->affiliate);
    }

    /** @test */
    public function it_has_many_social_accounts()
    {
        $user = User::factory()->create();
        
        $socialAccounts = SocialAccount::factory()->count(2)->create(['user_id' => $user->id]);

        $this->assertCount(2, $user->socialAccounts);
        $this->assertEquals($socialAccounts->pluck('id'), $user->socialAccounts->pluck('id'));
    }

    /** @test */
    public function it_has_many_reviewed_reports()
    {
        $user = User::factory()->create();
        
        $reports = Report::factory()->count(3)->create(['reviewed_by' => $user->id]);

        $this->assertCount(3, $user->reviewedReports);
        $this->assertEquals($reports->pluck('id'), $user->reviewedReports->pluck('id'));
    }

    /** @test */
    public function it_identifies_admin_users()
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $user = User::factory()->create(['role' => 'user']);

        $this->assertTrue($admin->isAdmin());
        $this->assertFalse($user->isAdmin());
    }

    /** @test */
    public function it_assigns_valid_roles()
    {
        $user = User::factory()->create();

        $user->assignRole('admin');
        $this->assertEquals('admin', $user->role);

        $user->assignRole('user');
        $this->assertEquals('user', $user->role);

        $user->assignRole('affiliate');
        $this->assertEquals('affiliate', $user->role);
    }

    /** @test */
    public function it_rejects_invalid_roles()
    {
        $user = User::factory()->create();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid role: invalid');

        $user->assignRole('invalid');
    }

    /** @test */
    public function it_checks_user_roles()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $this->assertTrue($user->hasRole('admin'));
        $this->assertFalse($user->hasRole('user'));
        $this->assertFalse($user->hasRole('affiliate'));
    }

    /** @test */
    public function it_casts_email_verified_at_to_datetime()
    {
        $user = User::factory()->create([
            'email_verified_at' => '2023-12-31 23:59:59',
        ]);

        $this->assertInstanceOf(\Carbon\Carbon::class, $user->email_verified_at);
    }

    /** @test */
    public function it_hashes_password_automatically()
    {
        $password = 'password123';
        $user = User::factory()->create(['password' => $password]);

        $this->assertNotEquals($password, $user->password);
        $this->assertTrue(password_verify($password, $user->password));
    }

    /** @test */
    public function it_handles_fillable_fields()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'avatar' => 'avatar.jpg',
        ];

        $user = User::create($data);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('avatar.jpg', $user->avatar);
        $this->assertTrue(password_verify('password123', $user->password));
    }

    /** @test */
    public function it_hides_sensitive_attributes()
    {
        $user = User::factory()->create([
            'password' => 'password123',
            'remember_token' => 'remember123',
        ]);

        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /** @test */
    public function it_uses_notifications()
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'notify'));
    }

    /** @test */
    public function it_handles_default_role()
    {
        $user = User::factory()->create();

        // Check if default role is set (depends on factory)
        $this->assertContains($user->role, ['user', 'admin', 'affiliate']);
    }

    /** @test */
    public function it_can_be_created_without_avatar()
    {
        $user = User::factory()->create([
            'avatar' => null,
        ]);

        $this->assertNull($user->avatar);
    }

    /** @test */
    public function it_preserves_email_case()
    {
        $user = User::factory()->create([
            'email' => 'John.Doe@Example.COM',
        ]);

        $this->assertEquals('John.Doe@Example.COM', $user->email);
    }

    /** @test */
    public function it_handles_null_email_verified_at()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->assertNull($user->email_verified_at);
    }

    /** @test */
    public function it_updates_role_correctly()
    {
        $user = User::factory()->create(['role' => 'user']);

        $user->assignRole('admin');

        $this->assertEquals('admin', $user->fresh()->role);
    }

    /** @test */
    public function it_handles_role_assignment_with_existing_role()
    {
        $user = User::factory()->create(['role' => 'admin']);

        $user->assignRole('user'); // Change role

        $this->assertEquals('user', $user->fresh()->role);
        $this->assertFalse($user->fresh()->isAdmin());
    }

    /** @test */
    public function it_authenticates_with_correct_password()
    {
        $password = 'password123';
        $user = User::factory()->create(['password' => $password]);

        $this->assertTrue(password_verify($password, $user->password));
    }

    /** @test */
    public function it_does_not_authenticate_with_wrong_password()
    {
        $user = User::factory()->create(['password' => 'password123']);

        $this->assertFalse(password_verify('wrongpassword', $user->password));
    }
}
