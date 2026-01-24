<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ShippingService;
use App\Services\RajaOngkirService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class MandatoryInsuranceTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $product;
    protected $shippingServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // Create Role if not exists
        if (!Role::where('name', 'SILVERCHANNEL')->exists()) {
            Role::create(['name' => 'SILVERCHANNEL', 'guard_name' => 'web']);
        }

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
            // Profile picture optional
        ]);
        
        $this->user->assignRole('SILVERCHANNEL');

        $this->product = Product::factory()->create([
            'price_silverchannel' => 100000,
            'weight' => 1000,
            'stock' => 100
        ]);
        
        // Setup Cart
        $this->user->cart()->create([
            'product_id' => $this->product->id,
            'quantity' => 1
        ]);

        // Mock RajaOngkirService directly as it is returned by ShippingService
        $providerMock = Mockery::mock(RajaOngkirService::class);
        $providerMock->shouldReceive('getCost')
            ->andReturn([
                [
                    'code' => 'jne',
                    'costs' => [
                        [
                            'service' => 'REG',
                            'cost' => [['value' => 10000, 'etd' => '1-2', 'note' => '']]
                        ]
                    ]
                ]
            ]);

        // Mock ShippingService
        $this->shippingServiceMock = Mockery::mock(ShippingService::class);
        $this->shippingServiceMock->shouldReceive('getProvider')
            ->andReturn($providerMock);
            
        $this->app->instance(ShippingService::class, $this->shippingServiceMock);
    }

    public function test_mandatory_insurance_applied_when_active()
    {
        // Enable Insurance
        SystemSetting::updateOrCreate(
            ['key' => 'shipping_insurance_active'], 
            ['value' => '1', 'type' => 'boolean', 'group' => 'shipping']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'shipping_insurance_percentage'], 
            ['value' => '1.0', 'type' => 'text', 'group' => 'shipping']
        ); // 1%
        
        // Enable Shipping Provider
        SystemSetting::updateOrCreate(
            ['key' => 'rajaongkir_active'], 
            ['value' => '1', 'type' => 'boolean', 'group' => 'shipping']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'shipping_provider'], 
            ['value' => 'rajaongkir', 'type' => 'text', 'group' => 'shipping']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'rajaongkir_origin_id'], 
            ['value' => '153', 'type' => 'text', 'group' => 'shipping']
        ); // Jakarta Selatan
        SystemSetting::updateOrCreate(
            ['key' => 'rajaongkir_couriers'], 
            ['value' => 'jne', 'type' => 'text', 'group' => 'shipping']
        );

        $response = $this->actingAs($this->user)
            ->postJson(route('silverchannel.checkout.process'), [
                'shipping_address' => [
                    'name' => 'Test User',
                    'phone' => '08123456789',
                    'province_id' => 1,
                    'city_id' => 1,
                    'subdistrict_id' => 1,
                    'address' => 'Jl. Test',
                    'province_name' => 'Test Province',
                    'city_name' => 'Test City',
                    'subdistrict_name' => 'Test Subdistrict',
                    'postal_code' => '12345',
                ],
                'shipping_service' => [
                    'courier' => 'jne',
                    'service' => 'REG',
                    'cost' => 10000
                ],
                'payment_method' => 'transfer',
                'notes' => 'Test Order',
                'use_insurance' => false // Should be ignored
            ]);

        $response->assertStatus(200); // Redirect to success/payment page or JSON success

        // Assert order created with insurance
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'insurance_amount' => 1000, // 1% of 100,000
            'shipping_cost' => 10000,
        ]);
    }

    public function test_mandatory_insurance_ignored_when_inactive()
    {
        // Disable Insurance
        SystemSetting::updateOrCreate(
            ['key' => 'shipping_insurance_active'], 
            ['value' => '0', 'type' => 'boolean', 'group' => 'shipping']
        );
        
        // Enable Shipping Provider
        SystemSetting::updateOrCreate(
            ['key' => 'rajaongkir_active'], 
            ['value' => '1', 'type' => 'boolean', 'group' => 'shipping']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'shipping_provider'], 
            ['value' => 'rajaongkir', 'type' => 'text', 'group' => 'shipping']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'rajaongkir_origin_id'], 
            ['value' => '153', 'type' => 'text', 'group' => 'shipping']
        );
        SystemSetting::updateOrCreate(
            ['key' => 'rajaongkir_couriers'], 
            ['value' => 'jne', 'type' => 'text', 'group' => 'shipping']
        );

        $response = $this->actingAs($this->user)
            ->postJson(route('silverchannel.checkout.process'), [
                'shipping_address' => [
                    'name' => 'Test User',
                    'phone' => '08123456789',
                    'province_id' => 1,
                    'city_id' => 1,
                    'subdistrict_id' => 1,
                    'address' => 'Jl. Test',
                    'province_name' => 'Test Province',
                    'city_name' => 'Test City',
                    'subdistrict_name' => 'Test Subdistrict',
                    'postal_code' => '12345',
                ],
                'shipping_service' => [
                    'courier' => 'jne',
                    'service' => 'REG',
                    'cost' => 10000
                ],
                'payment_method' => 'transfer',
                'notes' => 'Test Order',
                'use_insurance' => true // Should be ignored
            ]);

        $response->assertStatus(200);

        // Assert order created WITHOUT insurance
        $this->assertDatabaseHas('orders', [
            'user_id' => $this->user->id,
            'insurance_amount' => 0,
        ]);
    }
}
