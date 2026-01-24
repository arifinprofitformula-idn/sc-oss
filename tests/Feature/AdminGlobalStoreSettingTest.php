<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Store;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class AdminGlobalStoreSettingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Roles
        Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
    }

    public function test_admin_store_settings_validation()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');
        
        Store::create([
            'user_id' => $admin->id,
            'name' => 'EPI Center',
            'slug' => 'epi-center',
            'address' => 'Old Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345'
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.settings.store.update'), [
            'name' => '', // Required
        ]);

        $response->assertSessionHasErrors(['name']);
    }

    public function test_admin_store_settings_update_creates_audit_log()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');
        
        $store = Store::create([
            'user_id' => $admin->id,
            'name' => 'EPI Center',
            'slug' => 'epi-center',
            'address' => 'Old Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345'
        ]);

        $response = $this->actingAs($admin)->patch(route('admin.settings.store.update'), [
            'name' => 'EPI Center New',
            'address' => 'New Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'email' => 'admin@epi.com'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('stores', [
            'id' => $store->id,
            'name' => 'EPI Center New',
            'address' => 'New Address'
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'update_global_store_settings',
            'model_type' => Store::class,
            'model_id' => $store->id,
            'user_id' => $admin->id
        ]);
    }
}
