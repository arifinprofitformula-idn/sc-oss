<?php
declare(strict_types=1);

namespace App\Http\Controllers\Api\Admin\Rbac;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Rbac\Role as AppRole;
use App\Services\Email\EmailService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleApiController extends Controller
{
    public function assign(Request $request, User $user, EmailService $emails): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $requestedRoles = array_values(array_unique($validated['roles']));

        $existingRoles = $user->getRoleNames()->all();
        $finalRoles = array_values(array_unique(array_merge($existingRoles, $requestedRoles)));

        DB::transaction(function () use ($user, $finalRoles) {
            $user->syncRoles($finalRoles);
        });

        AuditLog::log('API_USER_ROLES_ASSIGNED', $user, ['roles' => $existingRoles], ['roles' => $finalRoles]);

        $emails->send('user_roles_updated', $user, [
            'changed_by' => auth()->user()?->email,
            'old_roles' => implode(', ', $existingRoles),
            'new_roles' => implode(', ', $finalRoles),
            'changed_at' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'user_id' => $user->id,
            'roles' => $finalRoles,
        ], 200);
    }

    public function update(Request $request, User $user, EmailService $emails): JsonResponse
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $newRoles = array_values(array_unique($validated['roles']));

        if (count($newRoles) === 0) {
            return response()->json([
                'error' => 'validation_error',
                'message' => 'User must have at least one role.',
            ], 422);
        }

        $actor = auth()->user();
        $isSelfUpdate = $actor && $actor->id === $user->id;

        if ($isSelfUpdate && !in_array('SUPER_ADMIN', $newRoles, true)) {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'You cannot remove SUPER_ADMIN role from yourself.',
            ], 403);
        }

        $oldRoles = $user->getRoleNames()->all();

        if (in_array('SUPER_ADMIN', $oldRoles, true) && !in_array('SUPER_ADMIN', $newRoles, true)) {
            $otherSuperAdminsExist = User::role('SUPER_ADMIN')
                ->where('id', '!=', $user->id)
                ->exists();

            if (!$otherSuperAdminsExist) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => 'System must have at least one SUPER_ADMIN user.',
                ], 403);
            }
        }

        DB::transaction(function () use ($user, $newRoles) {
            $user->syncRoles($newRoles);
        });

        $updatedRoles = $user->getRoleNames()->all();

        AuditLog::log('API_USER_ROLES_UPDATED', $user, ['roles' => $oldRoles], ['roles' => $updatedRoles]);

        $emails->send('user_roles_updated', $user, [
            'changed_by' => $actor?->email,
            'old_roles' => implode(', ', $oldRoles),
            'new_roles' => implode(', ', $updatedRoles),
            'changed_at' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'user_id' => $user->id,
            'roles' => $updatedRoles,
        ]);
    }

    public function destroy(Request $request, User $user, AppRole $role, EmailService $emails): JsonResponse
    {
        $allRoles = $user->getRoleNames()->all();

        if (!in_array($role->name, $allRoles, true)) {
            return response()->json([
                'error' => 'not_found',
                'message' => 'Role not attached to user.',
            ], 404);
        }

        $remainingRoles = array_values(array_diff($allRoles, [$role->name]));

        if (count($remainingRoles) === 0) {
            return response()->json([
                'error' => 'validation_error',
                'message' => 'User must have at least one role.',
            ], 422);
        }

        $actor = auth()->user();
        $isSelfUpdate = $actor && $actor->id === $user->id;

        if ($isSelfUpdate && $role->name === 'SUPER_ADMIN') {
            return response()->json([
                'error' => 'forbidden',
                'message' => 'You cannot remove SUPER_ADMIN role from yourself.',
            ], 403);
        }

        if ($role->name === 'SUPER_ADMIN' && $user->hasRole('SUPER_ADMIN')) {
            $otherSuperAdminsExist = User::role('SUPER_ADMIN')
                ->where('id', '!=', $user->id)
                ->exists();

            if (!$otherSuperAdminsExist) {
                return response()->json([
                    'error' => 'forbidden',
                    'message' => 'System must have at least one SUPER_ADMIN user.',
                ], 403);
            }
        }

        DB::transaction(function () use ($user, $role) {
            $user->removeRole($role->name);
        });

        $updatedRoles = $user->getRoleNames()->all();

        AuditLog::log('API_USER_ROLE_DETACHED', $user, ['roles' => $allRoles], ['roles' => $updatedRoles]);

        $emails->send('user_roles_updated', $user, [
            'changed_by' => $actor?->email,
            'old_roles' => implode(', ', $allRoles),
            'new_roles' => implode(', ', $updatedRoles),
            'changed_at' => now()->toDateTimeString(),
        ]);

        return response()->json([
            'user_id' => $user->id,
            'roles' => $updatedRoles,
        ]);
    }
}

