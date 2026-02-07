<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Package;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExclusiveRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup necessary data for SilverChannelRegistrationController
        Package::factory()->create(['name' => 'Silver Package', 'price' => 1000000]);
        Store::factory()->create(); // Assuming Store model exists and is needed
    }

    public function test_registration_page_is_forbidden_without_referral_code()
    {
        $response = $this->get(route('register'));
        $response->assertStatus(403);
        $response->assertSee('invitation only');
    }

    public function test_registration_page_is_forbidden_with_invalid_referral_code()
    {
        $response = $this->get(route('register', ['ref' => 'INVALID_CODE']));
        $response->assertStatus(403);
        $response->assertSee('Invalid referral code');
    }

    public function test_registration_page_is_accessible_with_valid_referral_code()
    {
        $referrer = User::factory()->create([
            'referral_code' => 'VALIDREF123',
            'name' => 'Referrer User',
            'email' => 'referrer@example.com',
            'password' => bcrypt('password'),
        ]);

        $response = $this->get(route('register', ['ref' => 'VALIDREF123']));
        
        $response->assertStatus(200);
        $response->assertViewIs('auth.register-silver');
        $response->assertSee('VALIDREF123'); // Should be in hidden input
    }

    public function test_registration_submission_requires_referral_code()
    {
        // Even if someone bypasses GET, POST should fail middleware
        $response = $this->post(route('register.store'), [
            'name' => 'New User',
            'email' => 'new@example.com',
            // Missing referral
        ]);

        $response->assertStatus(403);
    }
}
