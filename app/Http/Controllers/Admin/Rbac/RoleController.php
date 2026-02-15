<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Rbac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rbac\Role;
use App\Models\AuditLog;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;
use App\Services\PermissionGroupService;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->paginate(20);
        $permissions = Permission::all();
        return view('admin.rbac.roles.index', compact('roles', 'permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permission_groups' => ['array'],
        ]);

        try {
            $role = DB::transaction(function () use ($validated) {
                $role = Role::create([
                    'name' => $validated['name'],
                    'guard_name' => 'web',
                    'description' => $validated['description'] ?? null,
                ]);
                $groupPerms = app(PermissionGroupService::class)->expandGroups($validated['permission_groups'] ?? []);
                $role->syncPermissions(array_unique(array_merge($validated['permissions'] ?? [], $groupPerms)));
                return $role->load('permissions');
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            AuditLog::log('ROLE_CREATED', $role, null, [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->all(),
            ]);

            return redirect()->route('admin.rbac.roles.index')->with('status', 'Role created');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors([
                'general' => 'Failed to create role. Please try again.',
            ]);
        }
    }

    public function edit(Role $role)
    {
        $permissions = Permission::all();
        return view('admin.rbac.roles.edit', compact('role', 'permissions'));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
            'permission_groups' => ['array'],
        ]);

        $oldValues = [
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name')->all(),
        ];

        try {
            $updatedRole = DB::transaction(function () use ($role, $validated) {
                $role->update([
                    'description' => $validated['description'] ?? $role->description,
                ]);

                if (isset($validated['permissions']) || isset($validated['permission_groups'])) {
                    $groupPerms = app(PermissionGroupService::class)->expandGroups($validated['permission_groups'] ?? []);
                    $perms = array_unique(array_merge($validated['permissions'] ?? [], $groupPerms));
                    $role->syncPermissions($perms);
                }

                return $role->load('permissions');
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            AuditLog::log('ROLE_UPDATED', $updatedRole, $oldValues, [
                'description' => $updatedRole->description,
                'permissions' => $updatedRole->permissions->pluck('name')->all(),
            ]);

            return redirect()->route('admin.rbac.roles.index')->with('status', 'Role updated');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withInput()->withErrors([
                'general' => 'Failed to update role. Please try again.',
            ]);
        }
    }

    public function destroy(Role $role)
    {
        $oldValues = [
            'name' => $role->name,
            'description' => $role->description,
            'permissions' => $role->permissions->pluck('name')->all(),
        ];

        try {
            $role->delete();

            AuditLog::log('ROLE_DELETED', $role, $oldValues, null);

            return redirect()->route('admin.rbac.roles.index')->with('status', 'Role deleted');
        } catch (\Throwable $e) {
            report($e);
            return redirect()->back()->withErrors([
                'general' => 'Failed to delete role. Please try again.',
            ]);
        }
    }
}
