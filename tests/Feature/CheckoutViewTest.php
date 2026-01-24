<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use App\Models\Cart;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Database\Seeders\PermissionSeeder;
use Illuminate\Support\Facades\View;

class CheckoutViewTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create user
        $this->user = User::factory()->create([
            'status' => 'ACTIVE',
            'phone' => '08123456789',
            'nik' => '1234567890123456',
            'address' => 'Jl. Test No. 123',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'religion' => 'islam',
            'marital_status' => 'single',
            'job' => 'developer',
            'address_provider' => 'rajaongkir',
        ]);
        
        $role = Role::firstOrCreate(['name' => 'SILVERCHANNEL']);
        $this->user->assignRole($role);
        
        dump('Completeness: ' . $this->user->profile_completeness);
    }

    public function test_checkout_view_renders_correctly()
    {
        $this->actingAs($this->user);

        // Add item to cart
        $product = Product::factory()->create();
        Cart::create([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price_final,
        ]);

        // Mock System Settings
        SystemSetting::updateOrCreate(['key' => 'shipping_insurance_active'], ['value' => '1', 'type' => 'boolean', 'group' => 'shipping']);

        // Mock View Data
        // Since we are testing the controller response, we can just hit the route.
        // Assuming the route is 'silverchannel.checkout.index' or similar.
        // Let's check routes file or just guess '/checkout'.
        
        $response = $this->get(route('silverchannel.checkout.index'));

        if ($response->status() === 302) {
            dump($response->headers->get('Location'));
        }

        $response->assertStatus(200);
        $response->assertSee('Daftar Produk');
        $response->assertSee('Biaya Asuransi Pengiriman (LM)');
    }
}
