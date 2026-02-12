<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PasswordReset;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class EnhancedPasswordResetLinkController extends Controller
{
    private EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.enhanced-forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        // Validasi email dengan ketat
        $validated = $request->validate([
            'email' => [
                'required',
                'email:rfc,dns,spoof,filter',
                'max:255',
                function ($attribute, $value, $fail) {
                    // Cek apakah email memiliki domain yang valid
                    $domain = substr(strrchr($value, "@"), 1);
                    if (!checkdnsrr($domain, "MX")) {
                        $fail('Email domain tidak valid atau tidak memiliki mail server.');
                    }
                }
            ],
        ], [
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.max' => 'Email tidak boleh lebih dari 255 karakter.',
        ]);

        $email = $validated['email'];

        // Rate limiting: maksimal 3 kali per jam per email
        $rateLimitKey = "password-reset:{$email}";
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik."
            ]);
        }

        // Cek apakah user ada
        $user = User::where('email', $email)->first();
        if (!$user) {
            // Tetap hit rate limit untuk mencegah enumeration
            RateLimiter::hit($rateLimitKey, 3600); // 1 jam
            
            return redirect()->route('password.request.confirmation')
                ->with('status', 'Jika email tersebut terdaftar di sistem kami, kami akan mengirimkan link reset password.');
        }

        // Hit rate limit
        RateLimiter::hit($rateLimitKey, 3600); // 1 jam

        try {
            // Generate token yang aman
            $token = Str::random(64);
            $hashedToken = Hash::make($token);
            $expiresAt = now()->addHour();

            // Simpan token ke database
            DB::transaction(function () use ($user, $hashedToken, $expiresAt) {
                // Hapus token lama
                PasswordReset::where('email', $user->email)->delete();
                
                // Buat token baru
                PasswordReset::create([
                    'email' => $user->email,
                    'token' => $hashedToken,
                    'created_at' => now(),
                    'expires_at' => $expiresAt,
                ]);
            });

            // Kirim email dengan retry mechanism
            $this->sendResetEmailWithRetry($user, $token);

            // Log success
            Log::info('Password reset email sent successfully', [
                'email' => $email,
                'user_id' => $user->id,
            ]);

            return redirect()->route('password.request.confirmation')
                ->with('status', 'Link reset password telah dikirim ke email Anda. Link akan kadaluarsa dalam 1 jam.');

        } catch (\Exception $e) {
            // dump($e->getMessage());
            // Log error detail untuk debugging
            Log::error('Password reset email failed', [
                'email' => $email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            // Hapus token jika email gagal
            PasswordReset::where('email', $email)->delete();

            // Tampilkan pesan error user-friendly
            return redirect()->route('password.request.error')
                ->with('error', 'Permohonan maaf, saat ini permintaan tidak dapat diproses karena terjadi kesalahan. Silakan coba beberapa saat lagi.');
        }
    }

    /**
     * Kirim email reset password dengan retry mechanism
     */
    private function sendResetEmailWithRetry(User $user, string $token, int $attempt = 1): void
    {
        $maxAttempts = 3;
        $delay = $attempt * 5; // Exponential backoff: 5s, 10s, 15s

        try {
            $resetUrl = route('password.reset.enhanced', [
                'token' => $token,
                'email' => $user->email,
            ]);

            // Gunakan EmailService untuk kirim email
            $this->emailService->send('forgot_password', $user, [
                'name' => $user->name,
                'email' => $user->email,
                'reset_url' => $resetUrl,
                'count' => 60, // 60 menit
            ]);

        } catch (\Exception $e) {
            // dump($e->getTraceAsString());
            Log::warning("Password reset email attempt {$attempt} failed", [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            if ($attempt < $maxAttempts) {
                // Retry dengan delay
                sleep($delay);
                $this->sendResetEmailWithRetry($user, $token, $attempt + 1);
            } else {
                // Max attempts reached
                Log::error('Password reset email failed after max attempts', [
                    'user_id' => $user->id,
                    'final_error' => $e->getMessage(),
                ]);
                throw $e;
            }
        }
    }

    /**
     * Tampilkan halaman konfirmasi
     */
    public function confirmation(): View
    {
        return view('auth.password-reset-confirmation');
    }

    /**
     * Tampilkan halaman error
     */
    public function error(): View
    {
        return view('auth.password-reset-error');
    }
}