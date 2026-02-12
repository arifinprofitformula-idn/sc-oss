<?php

namespace Tests\Unit\Auth;

use App\Http\Controllers\Auth\EnhancedPasswordResetLinkController;
use App\Models\User;
use App\Services\Email\EmailService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\TestCase;

class EnhancedPasswordResetLinkControllerTest extends TestCase
{
    use RefreshDatabase;

    private EnhancedPasswordResetLinkController $controller;
    private EmailService|MockObject $emailService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->emailService = $this->createMock(EmailService::class);
        $this->app->instance(EmailService::class, $this->emailService);
        $this->controller = new EnhancedPasswordResetLinkController($this->emailService);
    }

    /** @test */
    public function it_validates_email_format_strictly()
    {
        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'invalid-email'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_applies_rate_limiting_to_password_reset_requests()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        // Make 3 requests (limit)
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(route('password.email.enhanced'), [
                'email' => 'test@gmail.com'
            ]);
        }

        // 4th request should be rate limited
        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'test@gmail.com'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('Terlalu banyak percobaan', $response->json('errors.email.0'));
    }

    /** @test */
    public function it_generates_secure_token_for_valid_user()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        $this->emailService->expects($this->once())
            ->method('send')
            ->with(
                $this->equalTo('forgot_password'),
                $this->callback(function ($u) use ($user) {
                    return $u->id === $user->id;
                }),
                $this->callback(function ($data) {
                    return isset($data['reset_url']);
                })
            );

        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'test@gmail.com'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.request.confirmation'));
        
        $this->assertDatabaseHas('password_resets', [
            'email' => 'test@gmail.com'
        ]);
    }

    /** @test */
    public function it_shows_error_for_non_existent_email()
    {
        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'nonexistent@gmail.com'
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('Email atau Pengguna tidak terdaftar dalam sistem', $response->json('errors.email.0'));
        
        // Should not create password reset record for non-existent email
        $this->assertDatabaseMissing('password_resets', [
            'email' => 'nonexistent@gmail.com'
        ]);
    }

    /** @test */
    public function it_handles_email_sending_errors_gracefully()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        $this->emailService->expects($this->exactly(3))
            ->method('send')
            ->willThrowException(new \Exception('SMTP Error'));

        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'test@gmail.com'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.request.error'));
        
        // Should not create password reset record on error
        $this->assertDatabaseMissing('password_resets', [
            'email' => 'test@gmail.com'
        ]);
    }

    /** @test */
    public function it_retries_email_sending_on_failure()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        // First 2 attempts fail, 3rd succeeds
        $this->emailService->expects($this->exactly(3))
            ->method('send')
            ->willReturnOnConsecutiveCalls(
                $this->throwException(new \Exception('First attempt failed')),
                $this->throwException(new \Exception('Second attempt failed')),
                null // Success on third attempt
            );

        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'test@gmail.com'
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('password.request.confirmation'));
    }

    /** @test */
    public function it_generates_token_with_correct_length()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        $this->emailService->expects($this->once())
            ->method('send')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->callback(function ($data) {
                    // Extract token from reset_url (path parameter)
                    // URL: .../reset-password/enhanced/{token}?email=...
                    $path = parse_url($data['reset_url'], PHP_URL_PATH);
                    $segments = explode('/', trim($path, '/'));
                    $token = end($segments);
                    return strlen($token) >= 64;
                })
            );

        $this->postJson(route('password.email.enhanced'), [
            'email' => 'test@gmail.com'
        ]);
    }

    /** @test */
    public function it_sets_correct_expiry_time()
    {
        $user = User::factory()->create([
            'email' => 'test@gmail.com'
        ]);

        $response = $this->postJson(route('password.email.enhanced'), [
            'email' => 'test@gmail.com'
        ]);

        $passwordReset = \App\Models\PasswordReset::where('email', 'test@gmail.com')->first();
        
        $this->assertNotNull($passwordReset);
        $this->assertNotNull($passwordReset->expires_at);
        $this->assertTrue($passwordReset->expires_at->isFuture());
        $this->assertTrue($passwordReset->expires_at->diffInMinutes() <= 60);
    }
}