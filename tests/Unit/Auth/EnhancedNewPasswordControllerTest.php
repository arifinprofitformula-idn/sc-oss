<?php

namespace Tests\Unit\Auth;

use App\Http\Controllers\Auth\EnhancedNewPasswordController;
use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EnhancedNewPasswordControllerTest extends TestCase
{
    use RefreshDatabase;

    private EnhancedNewPasswordController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new EnhancedNewPasswordController();
    }

    /** @test */
    public function it_validates_password_strength_requirements()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        // Test weak password
        $response = $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'weakpassword123',
            'password_confirmation' => 'weakpassword123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password']);
        $this->assertStringContainsString('Password harus mengandung minimal 1 huruf besar', $response->json('errors.password.0'));

        // Test strong password
        $response = $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_validates_password_confirmation_match()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'DifferentPass123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['password_confirmation']);
        $this->assertStringContainsString('Konfirmasi password tidak cocok', $response->json('errors.password_confirmation.0'));
    }

    /** @test */
    public function it_rejects_expired_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        // Create expired token
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now()->subHours(2),
            'expires_at' => now()->subHour(), // Expired
        ]);

        $response = $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('Token reset password tidak valid atau telah kadaluarsa', $response->json('errors.email.0'));
    }

    /** @test */
    public function it_rejects_invalid_token()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make('valid-token'),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->postJson(route('password.store.enhanced'), [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'StrongPass123!',
            'password_confirmation' => 'StrongPass123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
        $this->assertStringContainsString('Token reset password tidak valid atau telah kadaluarsa', $response->json('errors.email.0'));
    }

    /** @test */
    public function it_successfully_resets_password()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('old-password'),
        ]);
        
        $token = 'valid-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $response = $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Password berhasil direset. Silakan login dengan password baru Anda.');

        // Verify password was updated
        $user->refresh();
        $this->assertTrue(Hash::check('NewStrongPass123!', $user->password));

        // Verify token was deleted
        $this->assertDatabaseMissing('password_resets', [
            'email' => 'test@example.com',
        ]);
    }

    /** @test */
    public function it_invalidates_all_sessions_after_password_reset()
    {
        $user = User::factory()->create(['email' => 'test@example.com']);
        
        // Create some sessions
        DB::table('sessions')->insert([
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

        $token = 'valid-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ]);

        // Verify all sessions were deleted
        $this->assertDatabaseMissing('sessions', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function it_triggers_password_reset_event()
    {
        Event::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);
        $token = 'valid-token-1234567890123456789012345678901234567890123456789012345678901234';
        
        $passwordReset = PasswordReset::create([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
            'expires_at' => now()->addHour(),
        ]);

        $this->postJson(route('password.store.enhanced'), [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'NewStrongPass123!',
            'password_confirmation' => 'NewStrongPass123!',
        ]);

        Event::assertDispatched(\Illuminate\Auth\Events\PasswordReset::class, function ($event) use ($user) {
            return $event->user->id === $user->id;
        });
    }
}