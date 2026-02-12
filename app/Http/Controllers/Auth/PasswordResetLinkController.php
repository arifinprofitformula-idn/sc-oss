<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\View\View;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            return $status == Password::RESET_LINK_SENT
                        ? back()->with('status', __($status))
                        : back()->withInput($request->only('email'))
                            ->withErrors(['email' => __($status)]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Password reset email failed: ' . $e->getMessage());
            
            $errorMessage = 'Terjadi kesalahan saat mengirim email reset password.';
            
            // Check for connection/timeout issues
            if (str_contains(strtolower($e->getMessage()), 'timeout') || str_contains(strtolower($e->getMessage()), 'connection')) {
                $errorMessage = 'Gagal terhubung ke layanan email. Silakan coba beberapa saat lagi.';
            } 
            // Check for authentication issues
            elseif (str_contains(strtolower($e->getMessage()), 'auth') || str_contains(strtolower($e->getMessage()), 'credential')) {
                $errorMessage = 'Layanan email sedang dalam pemeliharaan (Auth Error). Silakan hubungi admin.';
            }

            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => $errorMessage]);
        }
    }
}
