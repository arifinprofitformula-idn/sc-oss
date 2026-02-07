<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'address',
        'province_id',
        'city_id',
        'subdistrict_id',
        'postal_code',
        'phone',
        'whatsapp',
        'email',
        'operating_hours',
        'is_open',
        'holiday_mode',
        'holiday_note',
        'notification_templates',
        'shipping_couriers',
        'bank_details',
        'payment_methods',
    ];

    protected $casts = [
        'social_links' => 'array',
        'operating_hours' => 'array',
        'notification_templates' => 'array',
        'shipping_couriers' => 'array',
        'bank_details' => 'array',
        'payment_methods' => 'array',
        'is_open' => 'boolean',
        'holiday_mode' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
