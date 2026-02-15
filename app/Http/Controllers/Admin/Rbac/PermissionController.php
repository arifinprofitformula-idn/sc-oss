<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Rbac;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rbac\Permission;
use Illuminate\Support\Facades\DB;

class PermissionController extends Controller
{
    public function index()
    {
        $permissions = Permission::paginate(20);
        return view('admin.rbac.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150', 'unique:permissions,name'],
            'description' => ['nullable', 'string'],
        ]);
        DB::transaction(function () use ($validated) {
            Permission::create([
                'name' => $validated['name'],
                'guard_name' => 'web',
                'description' => $validated['description'] ?? null,
            ]);
        });
        return redirect()->route('admin.rbac.permissions.index')->with('status', 'Permission created');
    }

    public function update(Request $request, Permission $permission)
    {
        $validated = $request->validate([
            'description' => ['nullable', 'string'],
        ]);
        $permission->update(['description' => $validated['description'] ?? null]);
        return redirect()->route('admin.rbac.permissions.index')->with('status', 'Permission updated');
    }

    public function destroy(Permission $permission)
    {
        $permission->delete();
        return redirect()->route('admin.rbac.permissions.index')->with('status', 'Permission deleted');
    }
}

