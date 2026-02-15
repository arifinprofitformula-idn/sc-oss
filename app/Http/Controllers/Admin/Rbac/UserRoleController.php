<?php
declare(strict_types=1);

namespace App\Http\Controllers\Admin\Rbac;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\AuditLog;
use App\Models\Rbac\Role;
use App\Services\Email\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserRoleController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->with('roles');

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        $users = $query->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.rbac.user-roles.index', compact('users'));
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $userRoles = $user->getRoleNames()->all();

        return view('admin.rbac.user-roles.edit', compact('user', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user, EmailService $emails)
    {
        $validated = $request->validate([
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $newRoles = array_values(array_unique($validated['roles']));

        if (count($newRoles) === 0) {
            return back()->withErrors(['general' => 'User must have at least one role.'])->withInput();
        }

        $actor = auth()->user();
        $isSelfUpdate = $actor && $actor->id === $user->id;

        if ($isSelfUpdate && !in_array('SUPER_ADMIN', $newRoles, true)) {
            return back()->withErrors(['general' => 'You cannot remove SUPER_ADMIN role from yourself.'])->withInput();
        }

        $oldRoles = $user->getRoleNames()->all();

        if (in_array('SUPER_ADMIN', $oldRoles, true) && !in_array('SUPER_ADMIN', $newRoles, true)) {
            $otherSuperAdminsExist = User::role('SUPER_ADMIN')
                ->where('id', '!=', $user->id)
                ->exists();

            if (!$otherSuperAdminsExist) {
                return back()->withErrors(['general' => 'System must have at least one SUPER_ADMIN user.'])->withInput();
            }
        }

        DB::transaction(function () use ($user, $newRoles) {
            $user->syncRoles($newRoles);
        });

        $updatedRoles = $user->getRoleNames()->all();

        AuditLog::log('USER_ROLES_UPDATED', $user, ['roles' => $oldRoles], ['roles' => $updatedRoles]);

        $emails->send('user_roles_updated', $user, [
            'changed_by' => $actor?->email,
            'old_roles' => implode(', ', $oldRoles),
            'new_roles' => implode(', ', $updatedRoles),
            'changed_at' => now()->toDateTimeString(),
        ]);

        return redirect()->route('admin.rbac.user-roles.edit', $user)->with('status', 'Roles updated successfully.');
    }
}

