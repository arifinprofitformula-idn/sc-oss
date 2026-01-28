<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'whatsapp',
        'nik',
        'silver_channel_id',
        'address',
        'province_id',
        'province_name',
        'city_id',
        'city_name',
        'subdistrict_id',
        'subdistrict_name',
        'village_id',
        'village_name',
        'postal_code',
        'address_provider',
        'referral_code',
        'referrer_id',
        'status',
        'password',
        'profile_picture',
        'gender',
        'birth_place',
        'birth_date',
        'job',
        'religion',
        'marital_status',
        'social_facebook',
        'social_instagram',
        'social_tiktok',
        'social_thread',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(User::class, 'referrer_id');
    }

    public function referralFollowUps(): HasMany
    {
        return $this->hasMany(ReferralFollowUp::class, 'referrer_id');
    }

    public function referralFollowUpAsReferred(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ReferralFollowUp::class, 'referred_user_id');
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function cart()
    {
        return $this->hasMany(Cart::class);
    }

    public function commissionLedgers()
    {
        return $this->hasMany(CommissionLedger::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }


    public function getWalletBalanceAttribute()
    {
        // Credits: Commissions that are AVAILABLE or PAID (if any positive paid exists)
        $credits = $this->commissionLedgers()
            ->where('amount', '>', 0)
            ->where('status', 'AVAILABLE')
            ->sum('amount');

        // Debits: Payouts that are PENDING (locked) or PAID/PROCESSED
        // We assume Payout Ledger entries are stored as NEGATIVE amounts
        $debits = $this->commissionLedgers()
            ->where('amount', '<', 0)
            ->whereIn('status', ['PENDING', 'PAID', 'APPROVED'])
            ->sum('amount');

        return $credits + $debits;
    }
    
    public function getPendingCommissionAttribute()
    {
        return $this->commissionLedgers()
             ->where('status', 'PENDING')
             ->where('amount', '>', 0)
             ->sum('amount');
    }

    public function getProfileCompletenessAttribute()
    {
        $fields = [
            'name', 'email', 'phone', 'nik', 'address',
            'province_id', 'city_id', 'subdistrict_id', 'postal_code',
            'birth_place', 'birth_date', 'gender', 'religion', 
            'marital_status', 'job', 'profile_picture',
            'bank_name', 'bank_account_no', 'bank_account_name'
        ];
        
        $filled = 0;
        foreach ($fields as $field) {
            if (!empty($this->$field)) {
                $filled++;
            }
        }
        
        return round(($filled / count($fields)) * 100);
    }
}
