<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\EmailTemplate;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class ForgotPasswordAuditTest extends TestCase
{
    use DatabaseTransactions;

    public function test_forgot_password_flow_sends_notification_with_correct_variables()
    {
        Notification::fake();

        // 1. Setup User
        $user = User::factory()->create([
            'email' => 'audit_test_' . time() . '@example.com',
            'name' => 'Audit User',
        ]);

        // 2. Setup Template (ensure it exists and has placeholders)
        // We update the existing one or create it, to ensure test consistency.
        // Using updateOrCreate with the key ensures we don't duplicate.
        EmailTemplate::updateOrCreate(
            ['key' => 'forgot_password'],
            [
                'subject' => 'Reset Password Audit',
                'body' => 'Hi {{name}}, click {{reset_url}}. Copyright {{year}} {{app_name}}. Logo: {{logo_url}} Support: {{support_email}}',
                'is_active' => true,
                'name' => 'Forgot Password',
            ]
        );

        // 3. Trigger Reset
        $response = $this->post(route('password.email'), ['email' => $user->email]);

        // 4. Assert Redirect (Success)
        $response->assertSessionHas('status', __(\Illuminate\Support\Facades\Password::RESET_LINK_SENT));

        // 5. Assert Notification Sent & Content Correct
        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class,
            function ($notification, $channels) use ($user) {
                // Manually generate the mail message
                $mailData = $notification->toMail($user);
                
                // The view is 'emails.html-template', and data is ['content' => ...]
                $viewData = $mailData->viewData;
                $content = $viewData['content'];

                // Check variable substitution
                $checks = [
                    'Name' => str_contains($content, 'Hi Audit User'),
                    'Year' => str_contains($content, 'Copyright ' . date('Y')),
                    'App Name' => str_contains($content, config('app.name')),
                    'Logo' => str_contains($content, 'Logo: ' . asset('images/logo.png')),
                    'Support' => str_contains($content, 'Support: ' . config('mail.from.address')),
                ];

                // We can't easily check the exact token in reset_url because it's hashed/generated inside,
                // but we can check the base route.
                $checks['Reset URL'] = str_contains($content, route('password.reset', ['token' => $notification->token, 'email' => $user->email], false));

                // If any check fails, dump it for debugging
                if (in_array(false, $checks)) {
                    dump($checks);
                    dump($content);
                }

                return !in_array(false, $checks);
            }
        );
    }

    public function test_handles_smtp_failure_gracefully()
    {
        // Mock Password facade to throw exception
        Password::shouldReceive('sendResetLink')
            ->once()
            ->andThrow(new \Exception('SMTP Auth Failed'));

        $user = User::factory()->create();

        $response = $this->post(route('password.email'), ['email' => $user->email]);

        // Assert generic error message in session errors
        $response->assertSessionHasErrors(['email' => 'Unable to send password reset link. Please verify your email configuration or try again later.']);
    }

    public function test_fails_for_non_existent_email()
    {
        $response = $this->post(route('password.email'), ['email' => 'nonexistent@example.com']);
        
        // Laravel default behavior: returns error if user not found
        $response->assertSessionHasErrors('email');
    }
}
