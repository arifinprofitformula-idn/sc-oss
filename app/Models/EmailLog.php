<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    protected $fillable = [
        'user_id',
        'distributor_id',
        'type',
        'template_id',
        'subject',
        'content',
        'to',
        'status',
        'provider_message_id',
        'queued_at',
        'sent_at',
        'delivered_at',
        'bounced_at',
        'opens_count',
        'clicks_count',
        'retry_count',
        'metadata',
        'error',
        'related_type',
        'related_id',
    ];

    protected $casts = [
        'queued_at' => 'datetime',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'bounced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function related()
    {
        return $this->morphTo();
    }
}
