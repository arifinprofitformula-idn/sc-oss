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
        $adminOperational = Role::firstOrCreate(['name' => 'ADMIN_OPERATIONAL']);
        $adminFinance = Role::firstOrCreate(['name' => 'ADMIN_FINANCE']);
        $customerService = Role::firstOrCreate(['name' => 'CUSTOMER_SERVICE']);
        $silverChannel = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
        
        $this->command->info('Roles checked/created: SUPER_ADMIN, ADMIN_OPERATIONAL, ADMIN_FINANCE, CUSTOMER_SERVICE, SILVERCHANNEL');

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

        // Operational Admin permissions
        $operationalPermissions = [
            'inventory.manage',
            'orders.manage',
            'products.manage',
            'vendors.manage',
            'reports.operational.view',
        ];

        foreach ($operationalPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Finance Admin permissions
        $financePermissions = [
            'finance.access',
            'sales.reports.view',
            'refund.manage',
            'payout.manage',
        ];

        foreach ($financePermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign Permissions
        $silverChannel->syncPermissions(Permission::whereIn('name', $scPermissions)->get());
        
        // Admin Operational gets operational permissions
        $adminOperational->syncPermissions(Permission::whereIn('name', $operationalPermissions)->get());

        // Admin Finance gets finance permissions
        $adminFinance->syncPermissions(Permission::whereIn('name', $financePermissions)->get());

        // Customer Service gets manage orders (chat) permissions (subset of admin)
        $csPermissions = ['complaints.manage', 'refund.request.manage', 'chat.access', 'faq.manage'];
        foreach ($csPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }
        $customerService->syncPermissions(Permission::whereIn('name', $csPermissions)->get());

        // Super Admin gets all permissions
        $superAdmin->syncPermissions(Permission::all());
    }
}
