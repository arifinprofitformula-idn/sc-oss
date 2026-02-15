<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionGroupSeeder extends Seeder
{
    public function run(): void
    {
        $groups = [
            'operational_core' => [
                'name' => 'Operational Core',
                'items' => [
                    'inventory.manage',
                    'orders.manage',
                    'products.manage',
                    'vendors.manage',
                    'reports.operational.view',
                ],
            ],
            'finance_core' => [
                'name' => 'Finance Core',
                'items' => [
                    'finance.access',
                    'sales.reports.view',
                    'refund.manage',
                    'payout.manage',
                ],
            ],
            'cs_core' => [
                'name' => 'Customer Service Core',
                'items' => [
                    'complaints.manage',
                    'refund.request.manage',
                    'chat.access',
                    'faq.manage',
                ],
            ],
        ];

        foreach ($groups as $code => $data) {
            $groupId = DB::table('permission_groups')->updateOrInsert(
                ['code' => $code],
                ['name' => $data['name'], 'updated_at' => now(), 'created_at' => now()]
            );
            $group = DB::table('permission_groups')->where('code', $code)->first();
            foreach ($data['items'] as $perm) {
                DB::table('permission_group_items')->updateOrInsert(
                    ['permission_group_id' => $group->id, 'permission_name' => $perm],
                    ['updated_at' => now(), 'created_at' => now()]
                );
            }
        }
    }
}

