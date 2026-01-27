<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TrackReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $referralCode = $request->get('ref');
            
            // Basic validation: string, max 20 chars (User model says max 10, but let's be safe)
            // We don't validate existence here to avoid DB queries on every request if it's global
            if (is_string($referralCode) && strlen($referralCode) <= 20) {
                // Queue the cookie for 30 days (30 * 24 * 60 minutes)
                Cookie::queue('referral_code', $referralCode, 30 * 24 * 60);
            }
        }

        return $next($request);
    }
}
