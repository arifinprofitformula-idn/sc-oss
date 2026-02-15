<?php
declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\DB;

class PermissionGroupService
{
    public function expandGroups(array $groupCodes): array
    {
        if (empty($groupCodes)) {
            return [];
        }
        $groupIds = DB::table('permission_groups')->whereIn('code', $groupCodes)->pluck('id');
        return DB::table('permission_group_items')
            ->whereIn('permission_group_id', $groupIds)
            ->pluck('permission_name')
            ->unique()
            ->values()
            ->all();
    }
}

