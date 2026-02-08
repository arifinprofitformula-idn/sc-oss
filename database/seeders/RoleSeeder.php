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
        
        $this->command->info('Resetting cached permissions...');

        // Create Roles
        $superAdmin = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $admin = Role::firstOrCreate(['name' => 'ADMIN']);
        $customerService = Role::firstOrCreate(['name' => 'CUSTOMER_SERVICE']);
        $silverChannel = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
        
        $this->command->info('Roles checked/created: SUPER_ADMIN, ADMIN, CUSTOMER_SERVICE, SILVERCHANNEL');

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
        
        // Admin gets admin permissions
        $admin->syncPermissions(Permission::whereIn('name', $adminPermissions)->get());

        // Customer Service gets manage orders (chat) permissions (subset of admin)
        $csPermissions = ['manage_orders'];
        $customerService->syncPermissions(Permission::whereIn('name', $csPermissions)->get());

        // Super Admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());
    }
}
