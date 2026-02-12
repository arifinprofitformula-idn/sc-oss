<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset as PasswordResetEvent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnhancedNewPasswordController extends Controller
{
    /**
     * Display the password reset view.
     */
    public function create(Request $request): View
    {
        // Validasi token
        $token = $request->route('token');
        $email = $request->email;

        if (!$token || !$email) {
            abort(404);
        }

        // Cek apakah token valid dan belum kadaluarsa
        $passwordReset = PasswordReset::where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            return view('auth.password-reset-invalid');
        }

        return view('auth.enhanced-reset-password', [
            'request' => $request,
            'token' => $token,
            'email' => $email,
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi input
        $validated = $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/',
            ],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 8 karakter.',
            'password.regex' => 'Password harus mengandung minimal 1 huruf besar, 1 huruf kecil, 1 angka, dan 1 karakter spesial.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',
            'password_confirmation.required' => 'Konfirmasi password wajib diisi.',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok.',
        ]);

        $token = $validated['token'];
        $email = $validated['email'];
        $password = $validated['password'];

        // Cek apakah token valid dan belum kadaluarsa
        $passwordReset = PasswordReset::where('email', $email)
            ->where('expires_at', '>', now())
            ->first();

        if (!$passwordReset || !Hash::check($token, $passwordReset->token)) {
            throw ValidationException::withMessages([
                'email' => 'Token reset password tidak valid atau telah kadaluarsa.'
            ]);
        }

        try {
            DB::transaction(function () use ($email, $password, $passwordReset) {
                // Update password user
                $user = User::where('email', $email)->first();
                if (!$user) {
                    throw new \Exception('User tidak ditemukan.');
                }

                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                // Hapus token reset
                $passwordReset->delete();

                // Invalidate semua session yang ada
                DB::table('sessions')->where('user_id', $user->id)->delete();

                // Trigger event
                event(new PasswordResetEvent($user));

                // Log success
                Log::info('Password reset successful', [
                    'user_id' => $user->id,
                    'email' => $email,
                ]);
            });

            return redirect()->route('login')
                ->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');

        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Terjadi kesalahan saat mereset password. Silakan coba lagi.']);
        }
    }
}