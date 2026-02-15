<?php
declare(strict_types=1);

namespace App\Observers;

use App\Models\Rbac\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RoleObserver
{
    public function created(Role $role): void
    {
        DB::table('role_versions')->insert([
            'role_id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'action' => 'created',
            'data_before' => null,
            'data_after' => json_encode($role->only(['name','guard_name','description'])),
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updated(Role $role): void
    {
        DB::table('role_versions')->insert([
            'role_id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'action' => 'updated',
            'data_before' => json_encode($role->getOriginal()),
            'data_after' => json_encode($role->getAttributes()),
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function deleted(Role $role): void
    {
        DB::table('role_versions')->insert([
            'role_id' => $role->id,
            'name' => $role->name,
            'guard_name' => $role->guard_name,
            'action' => 'deleted',
            'data_before' => json_encode($role->getOriginal()),
            'data_after' => null,
            'performed_by' => Auth::id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

