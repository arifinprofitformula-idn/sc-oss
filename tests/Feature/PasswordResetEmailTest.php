<?php

namespace Tests\Feature;

use App\Models\EmailTemplate;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered()
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_can_be_requested()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        Notification::assertSentTo(
            $user,
            ResetPasswordNotification::class
        );
    }

    public function test_reset_password_uses_custom_template()
    {
        Notification::fake();

        // Create template
        EmailTemplate::create([
            'key' => 'forgot_password',
            'name' => 'Forgot Password',
            'subject' => 'Custom Subject',
            'body' => 'Hello {{name}}, reset here: {{reset_url}}',
            'variables' => ['name', 'reset_url'],
        ]);

        $user = User::factory()->create();

        // Trigger notification manually to verify content (testing the notification class directly)
        $notification = new ResetPasswordNotification('test-token');
        $mailData = $notification->toMail($user);

        $this->assertEquals('Custom Subject', $mailData->subject);
        $this->assertStringContainsString('Hello ' . $user->name, $mailData->render());
    }

    public function test_rate_limiting()
    {
        // Mock Password facade to bypass internal 60s throttle
        \Illuminate\Support\Facades\Password::shouldReceive('sendResetLink')
            ->andReturn(\Illuminate\Support\Facades\Password::RESET_LINK_SENT);

        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $response = $this->post('/forgot-password', ['email' => $user->email]);
            $response->assertStatus(302);
            $response->assertSessionHas('status');
        }

        $response = $this->post('/forgot-password', ['email' => $user->email]);
        $response->assertStatus(429); // Too Many Requests
    }
}
