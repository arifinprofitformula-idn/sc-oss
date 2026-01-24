<?php

namespace Tests\Feature\Silverchannel;

use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use App\Models\Cart;
use App\Services\StoreOperationalService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $store;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Role
        Role::create(['name' => 'SILVERCHANNEL']);

        // Create User
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
            // Additional fields for profile completeness > 70%
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'religion' => 'Islam',
            'marital_status' => 'single',
            'job' => 'Entrepreneur',
        ]);
        
        $this->user->assignRole('SILVERCHANNEL');

        // Create Product
        $this->product = Product::factory()->create([
            'price_silverchannel' => 50000,
            'price_msrp' => 60000,
            'stock' => 10
        ]);

        // Create Default Store (Open)
        $this->store = Store::factory()->create([
            'is_open' => true,
        ]);
    }

    public function test_add_to_cart_fails_when_store_is_closed()
    {
        // Close the store
        $this->store->update(['is_open' => false]);
        
        // Clear cache to ensure service picks up new status
        (new StoreOperationalService())->refreshStatus();

        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('silverchannel.cart.store'), [
                'product_id' => $this->product->id,
                'quantity' => 1
            ]);

        // Expecting 403 Forbidden or 400 Bad Request depending on implementation
        // Assuming 403 based on "blocked" logic
        if ($response->status() !== 403 && $response->status() !== 400) {
             dump($response->getContent());
        }
        
        $response->assertStatus(403)
            ->assertJson(['message' => 'Toko sedang tutup. Tidak dapat menambah ke keranjang.']);
    }

    public function test_add_to_cart_succeeds_when_store_is_open()
    {
        // Ensure store is open
        $this->store->update(['is_open' => true]);
        (new StoreOperationalService())->refreshStatus();

        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('silverchannel.cart.store'), [
                'product_id' => $this->product->id,
                'quantity' => 1
            ]);

        $response->assertStatus(200);
    }

    public function test_add_to_cart_returns_correct_price_structure()
    {
        $response = $this->actingAs($this->user)
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson(route('silverchannel.cart.store'), [
                'product_id' => $this->product->id,
                'quantity' => 1
            ]);

        // Debug response if not 200
        if ($response->status() !== 200) {
            dump($response->getContent());
        }

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'item' => [
                    'id',
                    'product_id',
                    'name',
                    'price', // This is key
                    'image',
                    'stock',
                    'quantity'
                ]
            ]);

        $this->assertEquals(50000, $response->json('item.price'));
    }

    public function test_get_cart_items_returns_correct_price_structure()
    {
        // Add item to cart first
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->getJson(route('silverchannel.cart.items'));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'items' => [
                    '*' => [
                        'id',
                        'product_id',
                        'name',
                        'price',
                        'image',
                        'stock',
                        'quantity'
                    ]
                ],
                'subtotal',
                'count'
            ]);
            
        $this->assertEquals(50000, $response->json('items.0.price'));
        $this->assertEquals(100000, $response->json('subtotal'));
    }
}
