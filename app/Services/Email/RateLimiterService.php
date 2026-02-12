<?php
declare(strict_types=1);

namespace App\Services\Email;

use Illuminate\Support\Facades\RateLimiter;

class RateLimiterService
{
    public function allow(string $key, int $maxPerHour = 3): bool
    {
        return RateLimiter::attempt($key, $maxPerHour, function () {}, 3600);
    }
}

