<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Rbac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rbac\Role;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class RoleApiController extends Controller
{
    public function index()
    {
        return Role::with('permissions')->paginate(50);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
        ]);

        try {
            $role = DB::transaction(function () use ($validated) {
                $role = Role::create([
                    'name' => $validated['name'],
                    'guard_name' => 'web',
                    'description' => $validated['description'] ?? null,
                ]);
                $role->syncPermissions($validated['permissions'] ?? []);
                return $role->load('permissions');
            });

            app(PermissionRegistrar::class)->forgetCachedPermissions();

            AuditLog::log('API_ROLE_CREATED', $role, null, [
                'name' => $role->name,
                'description' => $role->description,
                'permissions' => $role->permissions->pluck('name')->all(),
            ]);

            return response()->json($role, 201);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'server_error',
                'message' => 'Failed to create role.',
            ], 500);
        }
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string'],
            'permissions' => ['array'],
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
                if (isset($validated['permissions'])) {
                    $role->syncPermissions($validated['permissions']);
                }
                return $role->load('permissions');
            });
            app(PermissionRegistrar::class)->forgetCachedPermissions();

            AuditLog::log('API_ROLE_UPDATED', $updatedRole, $oldValues, [
                'description' => $updatedRole->description,
                'permissions' => $updatedRole->permissions->pluck('name')->all(),
            ]);

            return response()->json($updatedRole);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'server_error',
                'message' => 'Failed to update role.',
            ], 500);
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

            AuditLog::log('API_ROLE_DELETED', $role, $oldValues, null);

            return response()->json(['status' => 'deleted']);
        } catch (\Throwable $e) {
            report($e);
            return response()->json([
                'error' => 'server_error',
                'message' => 'Failed to delete role.',
            ], 500);
        }
    }
}
