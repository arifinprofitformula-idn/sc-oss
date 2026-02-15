<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RbacAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_access_rbac_api(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'SUPER_ADMIN']);
        $user->assignRole('SUPER_ADMIN');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/rbac/roles')
            ->assertStatus(200);
    }

    public function test_non_super_admin_cannot_access_rbac_api(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['name' => 'ADMIN_OPERATIONAL']);
        $user->assignRole('ADMIN_OPERATIONAL');

        $this->actingAs($user, 'sanctum')
            ->getJson('/api/admin/rbac/roles')
            ->assertStatus(403);
    }

    public function test_permission_middleware_returns_informative_error(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->getJson('/api/admin/rbac/permissions');
        $response->assertStatus(403);
        $response->assertJsonStructure(['error','message','permission']);
    }
}

