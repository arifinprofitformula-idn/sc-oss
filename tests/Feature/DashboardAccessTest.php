<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create Roles if not exists
        if (!Role::where('name', 'SUPER_ADMIN')->exists()) {
            Role::create(['name' => 'SUPER_ADMIN']);
        }
        if (!Role::where('name', 'SILVERCHANNEL')->exists()) {
            Role::create(['name' => 'SILVERCHANNEL']);
        }
    }

    private function createCompleteUser()
    {
        return User::factory()->create([
            'status' => 'ACTIVE',
            'phone' => '08123456789',
            'nik' => '1234567890123456',
            'address' => 'Jl. Test No. 1',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'Belum Menikah',
            'job' => 'Wiraswasta',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User',
        ]);
    }

    public function test_admin_can_access_admin_dashboard()
    {
        $admin = $this->createCompleteUser();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.dashboard');
        $response->assertSee('Admin Dashboard');
        $response->assertSee('Total Users');
    }

    public function test_silverchannel_cannot_access_admin_dashboard()
    {
        $user = $this->createCompleteUser();
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($user)->get(route('admin.dashboard'));

        $response->assertStatus(403);
    }

    public function test_silverchannel_sees_referral_card_on_dashboard()
    {
        $user = $this->createCompleteUser();
        $user->referral_code = 'TESTREF123';
        $user->save();
        
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Link Referral Anda');
        $response->assertSee('TESTREF123');
        $response->assertSee('Copy Link');
        
        // Verify Alpine.js structure for robust copy
        $response->assertSee('navigator.clipboard.writeText', false); // Check modern API
        $response->assertSee('document.execCommand(\'copy\')', false); // Check fallback
        $response->assertSee('fallbackCopy()', false); // Check fallback function
        $response->assertSee('Menyalin...', false); // Check loading state text
        $response->assertSee('Tersalin!', false); // Check success state text
        $response->assertSee('Gagal', false); // Check error state text
    }

    public function test_admin_redirected_to_admin_dashboard_from_regular_dashboard()
    {
        $admin = $this->createCompleteUser();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_unauthenticated_user_redirected_to_login()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect(route('login'));
    }
}
