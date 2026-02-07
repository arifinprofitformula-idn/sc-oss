<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\User;
use App\Services\EpiAutoPriceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Illuminate\Support\Facades\Log;

class IntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $epiAutoPriceService;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);

        // Create admin user
        $this->admin = User::factory()->create();
        $this->admin->assignRole('SUPER_ADMIN');

        // Mock EpiAutoPriceService
        $this->epiAutoPriceService = $this->mock(EpiAutoPriceService::class);
        $this->epiAutoPriceService->shouldReceive('getSettings')->andReturn([
            'active' => true,
            'api_key' => 'test_key',
            'api_endpoint' => 'http://test.com',
            'base_url' => 'http://test.com',
            'update_interval' => 60,
            'notify_email' => 'admin@example.com',
            'mapping' => []
        ]);
        $this->epiAutoPriceService->shouldReceive('fetchAllPrices')->andReturn([]);
    }

    public function test_admin_can_view_epi_ape_integration_page()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.integrations.epi-ape'));

        $response->assertStatus(200);
        $response->assertViewIs('admin.integrations.epi-ape');
    }

    public function test_epi_ape_view_only_shows_active_products()
    {
        // Create active and inactive products
        $activeProduct = Product::factory()->create([
            'name' => 'Active Product',
            'is_active' => true,
        ]);

        $inactiveProduct = Product::factory()->create([
            'name' => 'Inactive Product',
            'is_active' => false,
        ]);

        $response = $this->actingAs($this->admin)->get(route('admin.integrations.epi-ape'));

        $response->assertStatus(200);
        
        // Assert active product is visible
        $response->assertSee('Active Product');
        
        // Assert inactive product is NOT visible
        $response->assertDontSee('Inactive Product');
    }

    public function test_inactive_products_are_logged_when_filtered()
    {
        // Create inactive product
        Product::factory()->create([
            'name' => 'Inactive Log Test',
            'is_active' => false,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'EPI APE View: Filtered out');
            });

        $this->actingAs($this->admin)->get(route('admin.integrations.epi-ape'));
    }
}
