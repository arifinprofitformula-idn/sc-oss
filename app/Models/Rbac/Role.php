<?php
declare(strict_types=1);

namespace App\Models\Rbac;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'guard_name',
        'description',
    ];
}

