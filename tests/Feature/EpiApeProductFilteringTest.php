<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class EpiApeProductFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup Role
        if (!Role::where('name', 'SUPER_ADMIN')->exists()) {
            Role::create(['name' => 'SUPER_ADMIN']);
        }
        
        $user = User::factory()->create();
        $user->assignRole('SUPER_ADMIN');
        $this->actingAs($user);
    }

    public function test_only_active_products_are_displayed_in_mapping_list()
    {
        // Arrange
        $activeProduct = Product::factory()->create([
            'name' => 'Active Product 123',
            'is_active' => true,
        ]);

        $inactiveProduct = Product::factory()->create([
            'name' => 'Inactive Product 456',
            'is_active' => false,
        ]);

        // Act
        $response = $this->get(route('admin.integrations.epi-ape'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('Active Product 123');
        $response->assertDontSee('Inactive Product 456');
    }

    public function test_inactive_products_exclusion_is_logged()
    {
        // Arrange
        Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message) {
                return str_contains($message, 'Filtered out 1 inactive products');
            });

        Product::factory()->create([
            'is_active' => true,
        ]);

        Product::factory()->create([
            'is_active' => false,
        ]);

        // Act
        $this->get(route('admin.integrations.epi-ape'));
    }

    public function test_empty_state_message_is_displayed_when_no_active_products()
    {
        // Arrange
        // Create only inactive product
        Product::factory()->create([
            'is_active' => false,
        ]);

        // Act
        $response = $this->get(route('admin.integrations.epi-ape'));

        // Assert
        $response->assertStatus(200);
        $response->assertSee('No active products found');
        $response->assertSee('Product Management');
    }
}
