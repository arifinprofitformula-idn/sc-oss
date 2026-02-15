<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Rbac\Permission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PermissionObserver
{
    public function created(Permission $permission): void
    {
        DB::table('permission_versions')->insert([
            'permission_id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'action' => 'created',
            'data_before' => null,
            'data_after' => json_encode($permission->only(['name','guard_name','description'])),
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updated(Permission $permission): void
    {
        DB::table('permission_versions')->insert([
            'permission_id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'action' => 'updated',
            'data_before' => json_encode($permission->getOriginal()),
            'data_after' => json_encode($permission->getAttributes()),
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function deleted(Permission $permission): void
    {
        DB::table('permission_versions')->insert([
            'permission_id' => $permission->id,
            'name' => $permission->name,
            'guard_name' => $permission->guard_name,
            'action' => 'deleted',
            'data_before' => json_encode($permission->getOriginal()),
            'data_after' => null,
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

