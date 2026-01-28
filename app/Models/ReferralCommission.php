<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralCommission extends Model
{
    use HasFactory;

    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'package_id',
        'amount',
        'commission_type',
        'commission_base_amount',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission_base_amount' => 'decimal:2',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser()
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}
