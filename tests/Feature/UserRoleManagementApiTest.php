<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserRoleManagementApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'ADMIN_OPERATIONAL', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ADMIN_OPERATIONAL', 'guard_name' => 'sanctum']);

        $this->superAdmin = User::factory()->create();
        $this->actingAs($this->superAdmin, 'web');
        $this->superAdmin->assignRole('SUPER_ADMIN');

        $this->targetUser = User::factory()->create();
    }

    public function test_super_admin_can_assign_additional_roles_to_user(): void
    {
        $this->targetUser->assignRole('ADMIN_OPERATIONAL');

        $response = $this->actingAs($this->superAdmin, 'sanctum')->postJson(
            '/api/admin/rbac/users/' . $this->targetUser->id . '/roles',
            [
                'roles' => ['SUPER_ADMIN'],
            ]
        );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $this->targetUser->id,
            ])
            ->assertJsonFragment([
                'roles' => ['ADMIN_OPERATIONAL', 'SUPER_ADMIN'],
            ]);
    }

    public function test_super_admin_can_replace_roles_for_user_with_validation(): void
    {
        $this->targetUser->assignRole('ADMIN_OPERATIONAL');

        $response = $this->actingAs($this->superAdmin, 'sanctum')->putJson(
            '/api/admin/rbac/users/' . $this->targetUser->id . '/roles',
            [
                'roles' => ['SUPER_ADMIN'],
            ]
        );

        $response->assertStatus(200)
            ->assertJsonFragment([
                'user_id' => $this->targetUser->id,
            ])
            ->assertJsonFragment([
                'roles' => ['SUPER_ADMIN'],
            ]);
    }

    public function test_cannot_remove_last_super_admin_role(): void
    {
        $response = $this->actingAs($this->superAdmin, 'sanctum')->putJson(
            '/api/admin/rbac/users/' . $this->superAdmin->id . '/roles',
            [
                'roles' => ['ADMIN_OPERATIONAL'],
            ]
        );

        $response->assertStatus(403);
    }

    public function test_non_super_admin_cannot_manage_roles_via_api(): void
    {
        $otherUser = User::factory()->create();
        $otherUser->assignRole('ADMIN_OPERATIONAL');

        $response = $this->actingAs($otherUser, 'sanctum')->postJson(
            '/api/admin/rbac/users/' . $this->targetUser->id . '/roles',
            [
                'roles' => ['ADMIN_OPERATIONAL'],
            ]
        );

        $response->assertStatus(403);
    }
}
