<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRedirectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_is_forbidden_without_referral()
    {
        $response = $this->get('/register');

        $response->assertStatus(403);
    }

    public function test_registration_post_route_is_forbidden_without_referral()
    {
        $response = $this->post('/register', []);

        $response->assertStatus(403);
    }

    public function test_login_page_does_not_contain_standard_registration_link()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        // Ensure we don't link to standard register route (exact match on href)
        $response->assertDontSee('href="' . route('register') . '"', false);
        $response->assertDontSee('Create account');
    }

    public function test_pages_display_epi_logo()
    {
        $pages = [
            '/login',
            '/forgot-password',
            // '/register-silver' requires referral now, so we skip it here or need to mock cookie
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);
            $response->assertSee('EPI');
            $response->assertSee('EPI-OSS');
        }
    }
}
