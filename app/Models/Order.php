<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes, HasFactory;

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
        'proof_of_delivery',
        'chat_assigned_to',
        'chat_tags',
        'chat_priority',
        'support_status',
        'support_closed_at',
        'notes',
        'payload',
    ];

    protected $casts = [
        'payload' => 'array',
        'chat_tags' => 'array',
        'paid_at' => 'datetime',
        'support_closed_at' => 'datetime',
    ];

    public function supportStatusHistories()
    {
        return $this->hasMany(SupportStatusHistory::class);
    }

    public function chatAssignee()
    {
        return $this->belongsTo(User::class, 'chat_assigned_to');
    }

    public function chatMessages()
    {
        return $this->hasMany(ChatMessage::class);
    }

    public function latestChatMessage()
    {
        return $this->hasOne(ChatMessage::class)->latestOfMany();
    }

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
