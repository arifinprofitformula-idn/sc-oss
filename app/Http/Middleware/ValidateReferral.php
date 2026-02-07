<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\User;
use Illuminate\Support\Facades\Cookie;

class ValidateReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Get referral code from URL or Cookie
        $refCode = $request->query('ref') ?? $request->cookie('referral_code');

        // 2. If no code, block access
        if (!$refCode) {
            abort(403, 'Registration is invitation only. Please use a valid referral link.');
        }

        // 3. Validate code existence
        if (!User::where('referral_code', $refCode)->exists()) {
            // If invalid, clear cookie if it exists to prevent stuck state
            if ($request->cookie('referral_code')) {
                Cookie::queue(Cookie::forget('referral_code'));
            }
            abort(403, 'Invalid referral code.');
        }

        // 4. If valid, we can ensure it's available for the controller
        // The controller checks query/cookie again, so we don't strictly need to pass it,
        // but let's ensure the cookie is set for future requests (persistence)
        if ($request->query('ref') && !$request->cookie('referral_code')) {
             Cookie::queue('referral_code', $refCode, 43200); // 30 days
        }

        return $next($request);
    }
}
