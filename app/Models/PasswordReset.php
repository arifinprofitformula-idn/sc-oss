<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    use HasFactory;

    protected $table = 'password_resets';

    public $timestamps = false;

    protected $fillable = [
        'email',
        'token',
        'created_at',
        'expires_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Cek apakah token masih valid
     */
    public function isValid(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }

    /**
     * Cek apakah token sudah kadaluarsa
     */
    public function isExpired(): bool
    {
        return !$this->isValid();
    }
}