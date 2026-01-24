<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class RootRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_redirects_to_login_for_guests()
    {
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Root access redirect' &&
                       isset($context['ip']) &&
                       $context['is_authenticated'] === false;
            });

        $response = $this->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('login'));
    }

    public function test_root_redirects_to_dashboard_for_authenticated_users()
    {
        // Buat user dummy yang sudah "profile completed" agar bisa akses dashboard
        // Note: Middleware 'active' mungkin diperlukan, asumsikan user aktif
        // Perlu cek User factory dan state
        
        // Kita mock user login
        $user = User::factory()->create([
            'status' => 'ACTIVE',
        ]);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) use ($user) {
                return $message === 'Root access redirect' &&
                       $context['is_authenticated'] === true &&
                       $context['user_id'] === $user->id;
            });

        $response = $this->actingAs($user)->get('/');

        $response->assertStatus(302);
        $response->assertRedirect(route('dashboard'));
    }
}
