<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Roles
        $superAdmin = Role::create(['name' => 'SUPER_ADMIN']);
        $silverChannel = Role::create(['name' => 'SILVERCHANNEL']);

        // Create Permissions
        // Silverchannel permissions
        $scPermissions = [
            'view_products',
            'create_order',
            'view_own_orders',
            'view_own_commissions',
        ];

        foreach ($scPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Admin permissions
        $adminPermissions = [
            'approve_silverchannel',
            'manage_products',
            'manage_orders',
            'manage_commissions',
            'view_reports',
        ];

        foreach ($adminPermissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Assign Permissions
        $silverChannel->givePermissionTo(Permission::whereIn('name', $scPermissions)->get());
        
        // Super Admin gets all permissions
        $superAdmin->givePermissionTo(Permission::all());
    }
}
