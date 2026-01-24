<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class StoreSettingMenuTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles
        $roleSuperAdmin = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $roleSilverchannel = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
    }

    protected function createCompleteUser()
    {
        return User::factory()->create([
            'status' => 'ACTIVE', // Ensure user is active to avoid 302 redirect
            'phone' => '081234567890',
            'nik' => '1234567890123456',
            'address' => 'Jl. Test No. 123',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'bank_name' => 'BCA',
            'bank_account_no' => '1234567890',
            'bank_account_name' => 'Test User',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'L',
            'religion' => 'Islam',
            'marital_status' => 'Belum Menikah',
            'job' => 'Wiraswasta',
        ]);
    }

    public function test_store_settings_menu_hidden_for_silverchannel_by_default()
    {
        $user = $this->createCompleteUser();
        $user->assignRole('SILVERCHANNEL');

        // Ensure setting is false/null
        SystemSetting::where('key', 'silverchannel_store_menu_active')->delete();

        $response = $this->actingAs($user)->get(route('silverchannel.store.settings'));
        
        if ($response->status() === 302) {
             dump('Redirect Location: ' . $response->headers->get('Location'));
             dump('User Status: ' . $user->status);
             dump('Profile Completeness: ' . $user->profile_completeness);
        }

        $response->assertStatus(403);
    }

    public function test_store_settings_menu_visible_when_enabled()
    {
        $user = $this->createCompleteUser();
        $user->assignRole('SILVERCHANNEL');

        // Enable setting
        $service = app(\App\Services\IntegrationService::class);
        $service->set('silverchannel_store_menu_active', 1, 'system', 'boolean');

        $response = $this->actingAs($user)->get(route('silverchannel.store.settings'));

        $response->assertStatus(200);
        $response->assertSee('Identitas Toko'); // Assuming this text exists in the view
    }

    public function test_super_admin_can_access_global_store_settings()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)->get(route('admin.settings.store'));

        $response->assertStatus(200);
        $response->assertSee('Global Store Settings');
    }

    public function test_super_admin_can_toggle_store_settings()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');
        
        // Create Store for Admin (usually done in Controller edit, but we need it for update validation if we don't pass all fields? No, create happens on edit or we can manually create)
        // Actually, the update method finds it by user_id. So we need to create it.
        \App\Models\Store::create([
            'user_id' => $admin->id,
            'name' => 'EPI Center',
            'slug' => 'epi-center',
            'address' => 'Old Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345'
        ]);

        // Enable it
        $response = $this->actingAs($admin)->patch(route('admin.settings.store.update'), [
            'silverchannel_store_menu_active' => '1',
            // Required fields for Store Update
            'name' => 'EPI Center Updated',
            'address' => 'Jl. Admin Pusat',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'email' => 'admin@epi.com',
            'phone' => '08123456789'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertEquals(1, app(\App\Services\IntegrationService::class)->get('silverchannel_store_menu_active'));
        
        // Disable it
        $response = $this->actingAs($admin)->patch(route('admin.settings.store.update'), [
            // 'silverchannel_store_menu_active' => '0', // Not sending it means false/0
            'name' => 'EPI Center Updated',
            'address' => 'Jl. Admin Pusat',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'email' => 'admin@epi.com',
            'phone' => '08123456789'
        ]);

        $response->assertRedirect();
        $this->assertEquals(0, app(\App\Services\IntegrationService::class)->get('silverchannel_store_menu_active'));
    }
}
