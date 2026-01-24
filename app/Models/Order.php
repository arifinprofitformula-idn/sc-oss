<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'order_number',
        'order_key',
        'total_amount',
        'subtotal',
        'tax_amount',
        'shipping_cost',
        'insurance_amount',
        'status', // DRAFT, SUBMITTED, WAITING_PAYMENT, WAITING_VERIFICATION, PAID, PACKING, SHIPPED, DELIVERED, CANCELLED
        'shipping_address',
        'shipping_courier',
        'shipping_service',
        'shipping_tracking_number',
        'payment_method',
        'notes',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function logs()
    {
        return $this->hasMany(OrderLog::class)->latest();
    }

    public function cart()
    {
        return $this->hasOne(Cart::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
