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
        $superAdmin = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $silverChannel = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);

        // Create Permissions
        // Silverchannel permissions
        $scPermissions = [
            'view_products',
            'create_order',
            'view_own_orders',
            'view_own_commissions',
        ];

        foreach ($scPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
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
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign Permissions
        $silverChannel->syncPermissions(Permission::whereIn('name', $scPermissions)->get());
        
        // Super Admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());
    }
}
