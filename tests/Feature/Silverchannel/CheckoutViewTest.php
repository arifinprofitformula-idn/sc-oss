<?php

namespace Tests\Feature\Silverchannel;

use App\Models\User;
use App\Models\Product;
use App\Models\Store;
use App\Services\IntegrationService;
use App\Services\StoreOperationalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CheckoutViewTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $integrationService;

    protected function setUp(): void
    {
        parent::setUp();
        
        Role::create(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);
        $this->user = User::factory()->create([
            'address_provider' => 'rajaongkir',
            'phone' => '08123456789',
            'address' => 'Jl. Test No. 123',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'village_id' => 1,
            'postal_code' => '12345',
            'nik' => '1234567890123456',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'job' => 'Developer',
            'status' => 'ACTIVE',
        ]);
        $this->user->assignRole('SILVERCHANNEL');
        $this->actingAs($this->user);

        // Mock StoreOperationalService
        $this->mock(StoreOperationalService::class, function ($mock) {
            $mock->shouldReceive('getStatus')->andReturn(['can_add_to_cart' => true]);
        });

        // Seed some products
        Product::factory()->count(3)->create();

        // Add item to cart
        $product = Product::first();
        DB::table('carts')->insert([
            'user_id' => $this->user->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        $this->integrationService = $this->mock(IntegrationService::class);
    }

    public function test_checkout_view_has_insurance_settings()
    {
        $this->integrationService->shouldReceive('get')
            ->with('shipping_provider', 'rajaongkir')
            ->andReturn('rajaongkir');

        $this->integrationService->shouldReceive('get')
            ->with('rajaongkir_api_key')
            ->andReturn('dummy_key');
        
        $this->integrationService->shouldReceive('get')
            ->with('rajaongkir_base_url')
            ->andReturn('https://api.rajaongkir.com/starter');

        $this->integrationService->shouldReceive('get')
            ->with('api_id_key')
            ->andReturn('dummy_api_id_key');
            
        $this->integrationService->shouldReceive('get')
            ->with('api_id_base_url', 'https://use.api.co.id')
            ->andReturn('https://use.api.co.id');

        $this->integrationService->shouldReceive('get')
            ->with('shipping_active_couriers', '[]')
            ->andReturn(json_encode(['jne']));
            
        $this->integrationService->shouldReceive('get')
            ->with('shipping_insurance_active', 0)
            ->andReturn(1);

        $this->integrationService->shouldReceive('get')
            ->with('shipping_insurance_percentage', 0)
            ->andReturn(0.5);

        $this->integrationService->shouldReceive('get')
            ->with('shipping_insurance_description', 'Layanan Asuransi Pengiriman')
            ->andReturn('Asuransi Paket');

        $this->integrationService->shouldReceive('get')
            ->with('silverchannel_store_menu_active')
            ->andReturn(true);
            
        $response = $this->get(route('silverchannel.checkout.index'));

        $response->assertStatus(200);
        $response->assertViewHas('insuranceSettings');
        
        $insuranceSettings = $response->viewData('insuranceSettings');
        $this->assertTrue($insuranceSettings['active']);
        $this->assertEquals(0.5, $insuranceSettings['percentage']);
        $this->assertEquals('Asuransi Paket', $insuranceSettings['description']);
    }
}
