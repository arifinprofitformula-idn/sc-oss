<?php

namespace Tests\Feature\Silverchannel;

use App\Models\Order;
use App\Models\Product;
use App\Models\StoreSetting;
use App\Models\User;
use App\Services\RajaOngkirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;
use Mockery;

class CheckoutProcessTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $rajaOngkirMock;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Role
        Role::create(['name' => 'SILVERCHANNEL']);

        // Create User with Profile
        $this->user = User::factory()->create([
            'status' => 'ACTIVE',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '08123456789',
            'address' => 'Jl. Merdeka No. 1',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'nik' => '1234567890123456',
            'birth_place' => 'Jakarta',
            'birth_date' => '1990-01-01',
            'gender' => 'Laki-laki',
            'religion' => 'Islam',
            'marital_status' => 'Menikah',
            'job' => 'Wiraswasta',
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
            'unique_code_enabled' => true,
            'unique_code_range_start' => 1,
            'unique_code_range_end' => 999,
        ]);
        
        // Mock RajaOngkir Service
        $this->rajaOngkirMock = Mockery::mock(RajaOngkirService::class);
        $this->app->instance(RajaOngkirService::class, $this->rajaOngkirMock);

        // Mock Integration Service for origin
        $integrationService = Mockery::mock(\App\Services\IntegrationService::class);
        $integrationService->shouldReceive('get')->with('rajaongkir_origin_id')->andReturn(152); // Jakarta Pusat
        $integrationService->shouldReceive('get')->with('api_id_key')->andReturn('test_key');
        $integrationService->shouldReceive('get')->with('api_id_base_url', 'https://use.api.co.id')->andReturn('https://use.api.co.id');
        $integrationService->shouldReceive('get')->with('shipping_provider', 'rajaongkir')->andReturn('rajaongkir');
        $integrationService->shouldReceive('get')->with('rajaongkir_active', 0)->andReturn(1);
        $integrationService->shouldReceive('get')->with('shipping_active_couriers', '[]')->andReturn('[]');
        $integrationService->shouldReceive('get')->with('rajaongkir_couriers', '')->andReturn('jne,pos,tiki');
        $integrationService->shouldReceive('get')->with('shipping_insurance_active', 0)->andReturn(0);
        $integrationService->shouldReceive('get')->with('shipping_insurance_percentage', 0)->andReturn(0);
        $this->app->instance(\App\Services\IntegrationService::class, $integrationService);
    }

    public function test_checkout_process_success()
    {
        // Set unique code in session
        session(['checkout_unique_code' => 123]);

        // Add item to cart
        $this->user->cart()->create([
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        // Mock Shipping Cost Calculation
        $this->rajaOngkirMock->shouldReceive('getCost')
            ->once()
            ->andReturn([
                [
                    'code' => 'jne',
                    'costs' => [
                        [
                            'service' => 'REG',
                            'cost' => [['value' => 10000, 'etd' => '1-2']]
                        ]
                    ]
                ]
            ]);

        $payload = [
            'ship_different' => false,
            'shipping_address' => [
                'name' => $this->user->name,
                'email' => $this->user->email,
                'phone' => $this->user->phone,
                'address' => $this->user->address,
                'subdistrict_id' => $this->user->subdistrict_id,
                'province_name' => 'DKI Jakarta',
                'city_name' => 'Jakarta Pusat',
                'subdistrict_name' => 'Gambir',
                'postal_code' => '10110'
            ],
            'shipping_service' => [
                'courier' => 'jne',
                'service' => 'REG',
                'cost' => 10000
            ],
            'payment_method' => 'transfer',
            'notes' => 'Test Order'
        ];

        $response = $this->actingAs($this->user)
            ->withSession(['checkout_unique_code' => 123])
            ->postJson(route('silverchannel.checkout.process'), $payload);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        // Assert Order Created
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'total_amount' => (50000 * 2) + 10000 + 123, // Base + Shipping + UniqueCode
            'status' => 'WAITING_PAYMENT'
        ]);

        // Assert Cart Empty
        $this->assertDatabaseMissing('carts', [
            'user_id' => $this->user->id
        ]);
    }
    
    public function test_checkout_process_validation_error()
    {
         $response = $this->actingAs($this->user)
            ->postJson(route('silverchannel.checkout.process'), []);
            
         $response->assertStatus(422);
    }
}
