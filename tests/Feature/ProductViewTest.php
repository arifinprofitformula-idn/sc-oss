<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
    }

    public function test_product_index_displays_customer_price_as_strikethrough()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product Customer Price',
            'price_silverchannel' => 100000,
            'price_customer' => 125000,
            'price_msrp' => 130000, // Should be ignored in favor of customer price
        ]);

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(200);
        
        // Assert Silverchannel price is displayed
        $response->assertSee(number_format(100000, 0, ',', '.'));
        
        // Assert Customer price is displayed
        $response->assertSee(number_format(125000, 0, ',', '.'));
        
        // Assert MSRP is NOT displayed (because customer price takes precedence)
        $response->assertDontSee(number_format(130000, 0, ',', '.'));
    }

    public function test_product_index_displays_msrp_if_customer_price_is_null()
    {
        $product = Product::factory()->create([
            'name' => 'Test Product MSRP',
            'price_silverchannel' => 100000,
            'price_customer' => null,
            'price_msrp' => 130000,
        ]);

        $response = $this->get(route('admin.products.index'));

        $response->assertStatus(200);
        
        // Assert Silverchannel price is displayed
        $response->assertSee(number_format(100000, 0, ',', '.'));
        
        // Assert MSRP is displayed
        $response->assertSee(number_format(130000, 0, ',', '.'));
    }
}
