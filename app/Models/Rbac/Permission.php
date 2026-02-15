<?php
declare(strict_types=1);

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];
}

