<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Rbac\Role as AppRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RbacRoleApiCrudTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin, 'web');
        $this->admin->assignRole('SUPER_ADMIN');
    }

    public function test_super_admin_can_create_role_via_api(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')->postJson('/api/admin/rbac/roles', [
            'name' => 'TEST_ROLE',
            'description' => 'Test role description',
            'permissions' => [],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment([
            'name' => 'TEST_ROLE',
            'description' => 'Test role description',
        ]);

        $this->assertDatabaseHas('roles', [
            'name' => 'TEST_ROLE',
            'description' => 'Test role description',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'API_ROLE_CREATED',
            'model_type' => AppRole::class,
        ]);
    }

    public function test_super_admin_can_update_role_via_api(): void
    {
        $role = AppRole::create([
            'name' => 'EDITABLE_ROLE',
            'guard_name' => 'web',
            'description' => 'Old description',
        ]);

        $permission = Permission::firstOrCreate([
            'name' => 'view_products',
            'guard_name' => 'web',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->putJson("/api/admin/rbac/roles/{$role->id}", [
            'description' => 'New description',
            'permissions' => [$permission->name],
        ]);

        $response->assertStatus(200);
        $response->assertJsonFragment([
            'id' => $role->id,
            'description' => 'New description',
        ]);

        $this->assertDatabaseHas('roles', [
            'id' => $role->id,
            'description' => 'New description',
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'API_ROLE_UPDATED',
            'model_type' => AppRole::class,
            'model_id' => $role->id,
        ]);
    }

    public function test_super_admin_can_delete_role_via_api(): void
    {
        $role = AppRole::create([
            'name' => 'DELETABLE_ROLE',
            'guard_name' => 'web',
            'description' => 'To be deleted',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')->deleteJson("/api/admin/rbac/roles/{$role->id}");

        $response->assertStatus(200);
        $response->assertJsonFragment(['status' => 'deleted']);

        $this->assertSoftDeleted('roles', [
            'id' => $role->id,
        ]);

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'API_ROLE_DELETED',
            'model_type' => AppRole::class,
            'model_id' => $role->id,
        ]);
    }
}
