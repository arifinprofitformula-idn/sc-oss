<?php

namespace Tests\Feature\Auth;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnhancedPasswordResetFlowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_complete_full_password_reset_flow()
    {
        // Create user
        $user = User::factory()->create([
            'email' => 'test@gmail.com',
            'password' => Hash::make('old-password'),
        ]);

        // Step 1: Request password reset
        $response = $this->post(route('password.email.enhanced'), [
            'email' => 'test@gmail.com',
        ]);

        $response->assertRedirect(route('password.request.confirmation'));
        $response->assertSessionHas('status');

        // Verify password reset record was created
        $this->assertDatabaseHas('password_resets', [
            'email' => 'test@gmail.com',
        ]);

        $passwordReset = PasswordReset::where('email', 'test@gmail.com')->first();
        
        // Extract token from database (since it's hashed)
        // In real scenario, you'd get this from email
        $token = 'test-token-1234567890123456789012345678901234567890123456789012345678901234';

        // Step 2: Access reset password form
        $response = $this->get(route('password.reset.enhanced', [
            'token' => $token,
            'email' => 'test@gmail.com',
        ]));

        $response->assertStatus(200);
        $response->assertViewIs('auth.enhanced-reset-password');

        // Step 3: Submit new password
        $response = $this->post(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');

        // Verify password was updated
        $user->refresh();
        $this->assertTrue(Hash::check('NewStrongPass123!', $user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_resets', [
            'email' => 'test@gmail.com',
        ]);
    }

    /** @test */
    public function it_prevents_password_reset_with_weak_password()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        // Create password reset token
        $token = 'test-token-1234567890123456789012345678901234567890123456789012345678901234';
        $passwordReset = PasswordReset::create([
            'email' => 'test@gmail.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        // Try with weak password
        $response = $this->post(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@gmail.com',
            'password' => 'weak123',
            'password_confirmation' => 'weak123',
        ]);

        $response->assertSessionHasErrors(['password']);
        
        // Verify password was not changed
        $user->refresh();
        $this->assertTrue(Hash::check('password', $user->password)); // Default factory password
    }

    /** @test */
    public function it_prevents_password_reset_with_expired_token()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        // Create expired password reset token
        $token = 'test-token-1234567890123456789012345678901234567890123456789012345678901234';
        $passwordReset = PasswordReset::create([
            'email' => 'test@gmail.com',
            'token' => Hash::make($token),
            'created_at' => now()->subHours(2),
            'expires_at' => now()->subHour(), // Expired
        ]);

        $response = $this->post(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@gmail.com',
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Token reset password tidak valid atau telah kadaluarsa', 
            session('errors')->first('email'));
    }

    /** @test */
    public function it_applies_rate_limiting_to_password_reset_requests()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);

        // Make 3 requests (limit)
        for ($i = 0; $i < 3; $i++) {
            $response = $this->post(route('password.email.enhanced'), [
                'email' => 'test@gmail.com',
            ]);
            $response->assertRedirect(route('password.request.confirmation'));
        }

        // 4th request should be rate limited
        $response = $this->post(route('password.email.enhanced'), [
            'email' => 'test@gmail.com',
        ]);

        $response->assertSessionHasErrors(['email']);
        $this->assertStringContainsString('Terlalu banyak percobaan', 
            session('errors')->first('email'));
    }

    /** @test */
    public function it_shows_user_friendly_error_page_on_email_failure()
    {
        // Mock email service to fail
        $this->mock(\App\Services\Email\EmailService::class)
            ->shouldReceive('send')
            ->andThrow(new \Exception('Email service error'));

        $user = User::factory()->create(['email' => 'test@gmail.com']);

        $response = $this->post(route('password.email.enhanced'), [
            'email' => 'test@gmail.com',
        ]);

        $response->assertRedirect(route('password.request.error'));
        $response->assertSessionHas('error', 'Permohonan maaf, saat ini permintaan tidak dapat diproses karena terjadi kesalahan. Silakan coba beberapa saat lagi.');
    }

    /** @test */
    public function it_validates_email_domain_strictly()
    {
        $response = $this->post(route('password.email.enhanced'), [
            'email' => 'test@nonexistentdomain12345.com',
        ]);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_includes_password_strength_requirements_in_reset_form()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        $token = 'test-token-1234567890123456789012345678901234567890123456789012345678901234';
        $passwordReset = PasswordReset::create([
            'email' => 'test@gmail.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->get(route('password.reset.enhanced', [
            'token' => $token,
            'email' => 'test@gmail.com',
        ]));

        $response->assertStatus(200);
        $response->assertSee('Minimal 8 karakter');
        $response->assertSee('Mengandung huruf besar');
        $response->assertSee('Mengandung huruf kecil');
        $response->assertSee('Mengandung angka');
        $response->assertSee('Mengandung karakter spesial');
    }

    /** @test */
    public function it_invalidates_all_sessions_after_password_reset()
    {
        $user = User::factory()->create(['email' => 'test@gmail.com']);
        
        // Create some sessions
        \Illuminate\Support\Facades\DB::table('sessions')->insert([
            [
                'id' => 'session1',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Browser',
                'payload' => 'test',
                'last_activity' => time(),
            ],
            [
                'id' => 'session2',
                'user_id' => $user->id,
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Test Browser 2',
                'payload' => 'test',
                'last_activity' => time(),
            ],
        ]);

        $token = 'test-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@gmail.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $this->post(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@gmail.com',
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ]);

        // Verify all sessions were deleted
        $this->assertDatabaseMissing('sessions', [
            'user_id' => $user->id,
        ]);
    }
}