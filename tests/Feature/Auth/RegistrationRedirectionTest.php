<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationRedirectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_route_redirects_to_silver_channel_registration()
    {
        $response = $this->get('/register');

        $response->assertRedirect(route('register.silver'));
    }

    public function test_registration_post_route_redirects_to_silver_channel_registration()
    {
        $response = $this->post('/register', []);

        $response->assertRedirect(route('register.silver'));
    }

    public function test_login_page_does_not_contain_registration_link()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee(route('register'));
        $response->assertDontSee('Create account');
    }

    public function test_pages_display_epi_logo()
    {
        $pages = [
            '/login',
            '/forgot-password',
            '/register-silver',
        ];

        foreach ($pages as $page) {
            $response = $this->get($page);
            $response->assertStatus(200);
            $response->assertSee('EPI');
            $response->assertSee('EPI-OSS');
        }
    }
}
