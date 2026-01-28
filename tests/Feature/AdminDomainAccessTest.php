<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDomainAccessTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        \Spatie\Permission\Models\Role::create(['name' => 'SUPER_ADMIN']);
        \Spatie\Permission\Models\Role::create(['name' => 'SILVERCHANNEL']);
    }

    public function test_admin_domain_root_redirects_to_admin_dashboard_for_super_admin()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->get('http://' . env('ADMIN_DOMAIN') . '/');

        // Currently, it probably redirects to 'dashboard' (user dashboard)
        // We want it to redirect to 'admin.silverchannels.index' or similar
        $response->assertRedirect(route('admin.silverchannels.index'));
    }

    public function test_admin_domain_root_redirects_to_login_for_guest()
    {
        $response = $this->get('http://' . env('ADMIN_DOMAIN') . '/');
        $response->assertRedirect(route('login'));
    }

    public function test_admin_dashboard_access()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->get(route('admin.silverchannels.index'));
            
        $response->assertStatus(200);
    }

    public function test_admin_path_redirects_to_dashboard()
    {
        $admin = User::factory()->create();
        $admin->assignRole('SUPER_ADMIN');

        $response = $this->actingAs($admin)
            ->get('http://' . env('ADMIN_DOMAIN') . '/admin');

        $response->assertRedirect(route('admin.silverchannels.index'));
    }
}
