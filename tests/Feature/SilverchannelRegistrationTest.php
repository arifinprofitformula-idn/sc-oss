<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use Database\Seeders\RoleSeeder;

class SilverchannelRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleSeeder::class);
    }

    public function test_registration_creates_pending_silverchannel_user()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '081234567890',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::where('email', 'test@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('PENDING_REVIEW', $user->status);
        $this->assertTrue($user->hasRole('SILVERCHANNEL'));
    }

    public function test_pending_user_cannot_access_dashboard()
    {
        $user = User::factory()->create([
            'status' => 'PENDING_REVIEW',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $this->actingAs($user);

        $response = $this->get('/dashboard');

        $response->assertRedirect(route('approval.notice'));
    }

    public function test_admin_can_approve_silverchannel()
    {
        $admin = User::factory()->create([
            'status' => 'ACTIVE',
        ]);
        $admin->assignRole('SUPER_ADMIN');

        $user = User::factory()->create([
            'status' => 'PENDING_REVIEW',
        ]);
        $user->assignRole('SILVERCHANNEL');

        $this->actingAs($admin);

        $response = $this->post(route('admin.silverchannels.approve', $user));

        $response->assertRedirect();
        
        $user->refresh();
        $this->assertEquals('ACTIVE', $user->status);
        $this->assertNotNull($user->referral_code);
    }

    public function test_registration_with_referral_code()
    {
        $referrer = User::factory()->create([
            'status' => 'ACTIVE',
            'referral_code' => 'REF123',
        ]);

        $response = $this->post('/register', [
            'name' => 'Referred User',
            'email' => 'referred@example.com',
            'phone' => '081234567891',
            'referral_code' => 'REF123',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'referred@example.com')->first();
        $this->assertEquals($referrer->id, $user->referrer_id);
    }
}
