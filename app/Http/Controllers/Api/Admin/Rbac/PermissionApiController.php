<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Rbac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rbac\Permission;

class PermissionApiController extends Controller
{
    public function index()
    {
        return Permission::paginate(100);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name'],
            'description' => ['nullable', 'string'],
        ]);
        $permission = Permission::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'description' => $validated['description'] ?? null,
        ]);
        return response()->json($permission, 201);
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string'],
        ]);
        $permission->update(['description' => $validated['description'] ?? null]);
        return response()->json($permission);
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return response()->json(['status' => 'deleted']);
    }
}

