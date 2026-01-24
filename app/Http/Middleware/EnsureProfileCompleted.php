<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureProfileCompleted
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Check if user is logged in
        if (!$user) {
            return $next($request);
        }

        // Check if user has Silverchannel role (or checks silver_channel_id if role logic is complex)
        // Using Spatie Permission
        if ($user->hasRole('SILVERCHANNEL')) {
            // Allow access to profile routes and logout to prevent infinite loops
            if ($request->routeIs('profile.*') || $request->routeIs('logout') || $request->routeIs('verification.*')) {
                return $next($request);
            }

            // Check profile completeness
            \Illuminate\Support\Facades\Log::info('Profile completeness check', ['user_id' => $user->id, 'score' => $user->profile_completeness]);
            
            if ($user->profile_completeness < 70) {
                return redirect()->route('profile.edit')
                    ->with('error', 'Harap lengkapi minimal 70% data profil untuk mengakses menu ini.');
            }
        }

        return $next($request);
    }
}
