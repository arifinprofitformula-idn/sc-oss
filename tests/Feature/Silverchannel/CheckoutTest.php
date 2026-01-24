<?php

namespace Tests\Feature\Silverchannel;

use App\Models\Cart;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Role
        $role = Role::create(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);

        // Create User with full profile
        $this->user = User::factory()->create([
            'status' => 'ACTIVE',
            'phone' => '081234567890',
            'nik' => '1234567890123456',
            'address' => 'Jl. Test No. 123',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'profile_picture' => 'profile.jpg',
        ]);
        
        $this->user->assignRole('SILVERCHANNEL');

        // Create Product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price_silverchannel' => 50000,
            'price_msrp' => 60000,
            'weight' => 500,
            'image' => 'products/test.jpg',
            'is_active' => true,
        ]);
    }

    public function test_checkout_page_loads_with_cart_data()
    {
        // Add item to cart
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('silverchannel.checkout.index'));

        $response->assertStatus(200);
        $response->assertViewIs('silverchannel.checkout.index');

        // Assert View Data
        $response->assertViewHas('cartItems', function ($items) {
            return $items->count() === 1
                && $items->first()->product->id === $this->product->id
                && $items->first()->quantity === 2
                && $items->first()->total == 100000 // 50000 * 2
                && str_contains($items->first()->product->image_url, 'storage/products/test.jpg');
        });
    }

    public function test_checkout_page_empty_cart()
    {
        // No items in cart

        $response = $this->actingAs($this->user)
            ->get(route('silverchannel.checkout.index'));

        $response->assertStatus(200);
        $response->assertViewHas('cartItems', function ($items) {
            return $items->isEmpty();
        });
    }

    public function test_checkout_page_requires_authentication()
    {
        $response = $this->get(route('silverchannel.checkout.index'));
        $response->assertRedirect(route('login'));
    }
}
