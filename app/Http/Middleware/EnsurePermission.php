<?php
declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePermission
{
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();
        if (!$user || !$user->can($permission)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => 'Akses ditolak: membutuhkan permission '.$permission,
                    'permission' => $permission,
                ], 403);
            }
            abort(403, 'Akses ditolak: membutuhkan permission '.$permission);
        }
        return $next($request);
    }
}

