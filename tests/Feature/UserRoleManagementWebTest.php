<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class UserRoleManagementWebTest extends TestCase
{
    use RefreshDatabase;

    protected User $superAdmin;
    protected User $targetUser;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'ADMIN_OPERATIONAL', 'guard_name' => 'web']);

        $this->superAdmin = User::factory()->create();
        $this->actingAs($this->superAdmin, 'web');
        $this->superAdmin->assignRole('SUPER_ADMIN');

        $this->targetUser = User::factory()->create();
        $this->targetUser->assignRole('ADMIN_OPERATIONAL');
    }

    public function test_super_admin_can_view_user_role_index(): void
    {
        $response = $this->actingAs($this->superAdmin)->get(route('admin.rbac.user-roles.index'));

        $response->assertStatus(200);
        $response->assertSee('Manajemen Role Pengguna');
    }

    public function test_super_admin_can_update_user_roles_via_web(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(
            route('admin.rbac.user-roles.update', $this->targetUser),
            [
                'roles' => ['SUPER_ADMIN'],
            ]
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('model_has_roles', [
            'model_id' => $this->targetUser->id,
            'model_type' => User::class,
        ]);
    }

    public function test_web_validation_prevents_empty_roles(): void
    {
        $response = $this->actingAs($this->superAdmin)->put(
            route('admin.rbac.user-roles.update', $this->targetUser),
            [
                'roles' => [],
            ]
        );

        $response->assertSessionHasErrors();
    }
}
