<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Super Admin bypass
            if ($user->hasRole('SUPER_ADMIN')) {
                return $next($request);
            }

            // If not active (case insensitive)
            if (strtoupper($user->status) !== 'ACTIVE') {
                // Allow access to approval notice and logout
                if ($request->routeIs('approval.notice') || $request->routeIs('logout')) {
                    return $next($request);
                }

                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Your account is pending approval.'], 403);
                }
                
                return redirect()->route('approval.notice');
            }
        }

        return $next($request);
    }
}
