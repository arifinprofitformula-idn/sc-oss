<?php
declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordResetToken;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class NewPasswordResetController extends Controller
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display the password reset link request form.
     */
    public function showRequestForm()
    {
        return view('auth.forgot-password-new');
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function sendResetLinkEmail(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email:rfc,dns', 'max:255'],
        ], [
            'email.required' => 'Email wajib diisi',
            'email.email' => 'Format email tidak valid',
            'email.max' => 'Email maksimal 255 karakter',
        ]);

        $email = strtolower(trim($validated['email']));

        // Rate limiting: 3 attempts per hour per email
        $rateLimitKey = "password-reset:{$email}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Silakan coba lagi dalam ' . $seconds . ' detik.',
            ]);
        }

        RateLimiter::hit($rateLimitKey, 3600); // 1 hour decay

        try {
            return DB::transaction(function () use ($email) {
                $user = User::where('email', $email)->first();

                if (!$user) {
                    // Don't reveal if email exists or not for security
                    Log::info('Password reset requested for non-existent email', ['email' => $email]);
                    return redirect()->route('password.request.confirmation')
                        ->with('status', 'reset_link_sent');
                }

                // Delete any existing tokens for this email
                PasswordResetToken::where('email', $email)->delete();

                // Generate secure token
                $token = Str::random(64);
                $hashedToken = Hash::make($token);

                // Store token with 1 hour expiry
                PasswordResetToken::create([
                    'email' => $email,
                    'token' => $hashedToken,
                    'created_at' => now(),
                ]);

                // Send email
                try {
                    $this->emailService->resetPassword($user, $token, 60);
                    
                    Log::info('Password reset email sent successfully', [
                        'email' => $email,
                        'user_id' => $user->id,
                    ]);
                } catch (\Exception $emailException) {
                    // Rollback token creation if email fails
                    PasswordResetToken::where('email', $email)->delete();
                    
                    Log::error('Failed to send password reset email', [
                        'email' => $email,
                        'error' => $emailException->getMessage(),
                        'trace' => $emailException->getTraceAsString(),
                    ]);

                    throw ValidationException::withMessages([
                        'email' => 'Permohonan maaf, saat ini permintaan tidak dapat diproses karena terjadi kesalahan. Silakan coba beberapa saat lagi.',
                    ]);
                }

                return redirect()->route('password.request.confirmation')
                    ->with('status', 'reset_link_sent');
            });
        } catch (\Exception $e) {
            Log::error('Password reset request failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($e instanceof ValidationException) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'email' => 'Terjadi kesalahan saat memproses permintaan. Silakan coba lagi.',
            ]);
        }
    }

    /**
     * Show confirmation page after reset link request.
     */
    public function showConfirmationPage()
    {
        if (!session('status') || session('status') !== 'reset_link_sent') {
            return redirect()->route('password.request');
        }

        return view('auth.password-reset-confirmation');
    }

    /**
     * Display the password reset form.
     */
    public function showResetForm(Request $request, ?string $token = null)
    {
        if (!$token) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Token reset password tidak valid.']);
        }

        return view('auth.reset-password-new', [
            'token' => $token,
            'email' => $request->email,
        ]);
    }

    /**
     * Handle password reset form submission.
     */
    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'min:64', 'max:64'],
            'email' => ['required', 'email', 'max:255'],
            'password' => [
                'required',
                'confirmed',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/',
            ],
            'password_confirmation' => ['required', 'same:password'],
        ], [
            'token.required' => 'Token reset password diperlukan',
            'token.min' => 'Token tidak valid',
            'token.max' => 'Token tidak valid',
            'email.required' => 'Email diperlukan',
            'email.email' => 'Format email tidak valid',
            'password.required' => 'Password baru diperlukan',
            'password.min' => 'Password minimal 8 karakter',
            'password.regex' => 'Password harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial',
            'password.confirmed' => 'Konfirmasi password tidak cocok',
            'password_confirmation.required' => 'Konfirmasi password diperlukan',
            'password_confirmation.same' => 'Konfirmasi password tidak cocok',
        ]);

        try {
            return DB::transaction(function () use ($validated) {
                $resetRecord = PasswordResetToken::where('email', $validated['email'])
                    ->where('created_at', '>', now()->subHour())
                    ->first();

                if (!$resetRecord) {
                    throw ValidationException::withMessages([
                        'email' => 'Token reset password telah kedaluwarsa atau tidak valid.',
                    ]);
                }

                // Verify token
                if (!Hash::check($validated['token'], $resetRecord->token)) {
                    throw ValidationException::withMessages([
                        'email' => 'Token reset password tidak valid.',
                    ]);
                }

                // Update password
                $user = User::where('email', $validated['email'])->first();
                if (!$user) {
                    throw ValidationException::withMessages([
                        'email' => 'User tidak ditemukan.',
                    ]);
                }

                $user->forceFill([
                    'password' => Hash::make($validated['password']),
                    'remember_token' => Str::random(60),
                ])->save();

                // Delete all tokens for this email
                PasswordResetToken::where('email', $validated['email'])->delete();

                // Log the password reset
                Log::info('Password reset successful', [
                    'user_id' => $user->id,
                    'email' => $validated['email'],
                ]);

                return redirect()->route('login')
                    ->with('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');
            });
        } catch (\Exception $e) {
            Log::error('Password reset failed', [
                'email' => $validated['email'] ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            if ($e instanceof ValidationException) {
                throw $e;
            }

            throw ValidationException::withMessages([
                'email' => 'Terjadi kesalahan saat mereset password. Silakan coba lagi.',
            ]);
        }
    }
}