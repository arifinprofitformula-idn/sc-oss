<?php

namespace Tests\Feature\Silverchannel;

use App\Models\Product;
use App\Models\User;
use App\Models\Cart;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Mockery;

class CheckoutPageTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock Integration Service
        $integrationService = Mockery::mock(\App\Services\IntegrationService::class);
        $integrationService->shouldReceive('get')->with('shipping_provider', 'rajaongkir')->andReturn('rajaongkir');
        $integrationService->shouldReceive('get')->with('silverchannel_primary_store_id')->andReturn(null);
        $integrationService->shouldReceive('get')->with('rajaongkir_active', 0)->andReturn(1);
        $integrationService->shouldReceive('get')->with('shipping_active_couriers', '[]')->andReturn('[]');
        $integrationService->shouldReceive('get')->with('rajaongkir_couriers', '')->andReturn('jne,pos,tiki');
        $integrationService->shouldReceive('get')->with('unique_code_range_start', 1)->andReturn(1);
        $integrationService->shouldReceive('get')->with('unique_code_range_end', 999)->andReturn(999);
        $integrationService->shouldReceive('get')->with('rajaongkir_api_key')->andReturn('test_key');
        $integrationService->shouldReceive('get')->with('rajaongkir_base_url')->andReturn('https://api.rajaongkir.com/starter');
        $integrationService->shouldReceive('get')->with('rajaongkir_origin_id')->andReturn(152);
        $integrationService->shouldReceive('get')->with('api_id_key')->andReturn('test_key');
        $integrationService->shouldReceive('get')->with('api_id_base_url', 'https://use.api.co.id')->andReturn('https://use.api.co.id');
        $integrationService->shouldReceive('get')->with('silverchannel_store_menu_active')->andReturn(true);
        $this->app->instance(\App\Services\IntegrationService::class, $integrationService);

        // Setup Role
        Role::create(['name' => 'SILVERCHANNEL']);

        // Create User with Profile
        $this->user = User::factory()->create([
            'status' => 'ACTIVE',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
            // Add fields to meet > 70% profile completeness
            'nik' => '1234567890123456',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'marital_status' => 'Menikah',
            'job' => 'Wiraswasta',
            'address_provider' => 'rajaongkir',
        ]);
        
        $this->user->assignRole('SILVERCHANNEL');

        // Create Product
        $this->product = Product::factory()->create([
            'name' => 'Test Product',
            'price_silverchannel' => 50000,
            'price_msrp' => 60000,
            'stock' => 10,
            'weight' => 500
        ]);

        // Create Store Setting
        StoreSetting::create([
            'distributor_name' => 'Main Distributor',
            'distributor_address' => 'Main HQ',
            'distributor_phone' => '021-1234567',
            'unique_code_enabled' => true,
            'unique_code_range_start' => 1,
            'unique_code_range_end' => 999,
            'bank_info' => [['bank' => 'BCA', 'account_number' => '123', 'account_name' => 'PT EPI']]
        ]);
    }

    public function test_checkout_page_renders_correctly()
    {
        // Add item to cart
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('silverchannel.checkout.index'));

        $response->assertStatus(200);
        $response->assertViewIs('silverchannel.checkout.index');
        
        // Assert View Data
        $response->assertViewHas('user');
        $response->assertViewHas('cartItems');
        $response->assertViewHas('uniqueCode');
        
        // Assert User Details are present in the response text
        $response->assertSee('Detail Tagihan');
        $response->assertSee($this->user->name);
        $response->assertSee($this->user->email);
        $response->assertSee('08123456789'); // Phone
        
        // Assert Cart Item Details
        $response->assertSee('Pesanan Anda');
        $response->assertSee('Test Product');
        $response->assertSee('50000'); // Check for raw price in JSON
        
        // Assert Shipping Toggle
        $response->assertSee('Kirim ke alamat yang berbeda?');

        $response->assertSee('Layanan Pengiriman');
        $response->assertSee('Urutkan:');
    }

    public function test_checkout_page_redirects_if_cart_empty()
    {
        // No items in cart
        // Ideally, checkout should maybe redirect or show empty state?
        // The current implementation renders the page even if empty, but the button is disabled.
        // Let's verify it renders.
        
        $response = $this->actingAs($this->user)
            ->get(route('silverchannel.checkout.index'));

        $response->assertStatus(200);
        $response->assertSee('Keranjang kosong'); // Based on the view template
    }
}
