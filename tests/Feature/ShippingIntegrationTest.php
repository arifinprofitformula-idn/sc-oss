<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\IntegrationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Spatie\Permission\Models\Role;

class ShippingIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $role = Role::create(['name' => 'SUPER_ADMIN']);
        $user = User::factory()->create();
        $user->assignRole($role);
        $this->actingAs($user);
    }

    public function test_can_update_shipping_insurance_settings()
    {
        \Illuminate\Support\Facades\Log::shouldReceive('info')
            ->once()
            ->withArgs(function ($message, $context) {
                return $message === 'Shipping/Integration Settings Updated' &&
                       isset($context['user_id']) &&
                       in_array('shipping_insurance_percentage', $context['changes']);
            });

        $response = $this->post(route('admin.integrations.update'), [
            'shipping_insurance_active' => '1',
            'shipping_insurance_percentage' => '0.5',
            'shipping_insurance_description' => 'Insurance Test'
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $integrationService = app(IntegrationService::class);
        $this->assertEquals('1', $integrationService->get('shipping_insurance_active'));
        $this->assertEquals('0.5', $integrationService->get('shipping_insurance_percentage'));
        $this->assertEquals('Insurance Test', $integrationService->get('shipping_insurance_description'));
    }

    public function test_shipping_insurance_percentage_validation()
    {
        $response = $this->post(route('admin.integrations.update'), [
            'shipping_insurance_percentage' => '101' // Invalid: > 100
        ]);

        $response->assertSessionHasErrors('shipping_insurance_percentage');
        
        $response = $this->post(route('admin.integrations.update'), [
            'shipping_insurance_percentage' => '-1' // Invalid: < 0
        ]);
        
        $response->assertSessionHasErrors('shipping_insurance_percentage');

        $response = $this->post(route('admin.integrations.update'), [
            'shipping_insurance_percentage' => 'abc' // Invalid: not numeric
        ]);
        
        $response->assertSessionHasErrors('shipping_insurance_percentage');
    }

    public function test_shipping_page_is_accessible()
    {
        $response = $this->get(route('admin.integrations.shipping'));
        $response->assertStatus(200);
        $response->assertSee('API Ongkir');
    }

    public function test_can_switch_shipping_provider()
    {
        $response = $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'api_id'
        ]);

        $response->assertRedirect();
        
        $integrationService = app(IntegrationService::class);
        $this->assertEquals('api_id', $integrationService->get('shipping_provider'));
    }

    public function test_can_update_api_id_settings()
    {
        $response = $this->post(route('admin.integrations.update'), [
            'api_id_key' => 'test-api-key',
            'api_id_active' => '1'
        ]);

        $response->assertRedirect();
        
        $integrationService = app(IntegrationService::class);
        // Encrypted values cannot be directly asserted as equals, but we check if it's set
        $this->assertNotEmpty($integrationService->get('api_id_key'));
        $this->assertEquals('1', $integrationService->get('api_id_active'));
    }

    public function test_api_id_cost_calculation_mock()
    {
        // Mock API ID response (Raw format expected by ApiIdService)
        Http::fake([
            'https://use.api.co.id/expedition/shipping-cost*' => Http::response([
                'data' => [
                    'couriers' => [
                        [
                            'courier_code' => 'jne',
                            'courier_name' => 'JNE Express',
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'price' => 10000,
                            'estimation' => '1-2 Days'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Set provider to api_id and set origin
        $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'api_id',
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id',
            'api_id_origin_id' => '1234567890', // 10 digit village code
            'api_id_origin_label' => 'Test Village'
        ]);

        // Test cost calculation endpoint
        $response = $this->post(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '0987654321',
            'weight' => 1000,
            'courier' => 'jne'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        // Verify structure returned to frontend (RajaOngkir-like structure)
        $response->assertJsonPath('data.0.code', 'jne');
        $response->assertJsonPath('data.0.costs.0.service', 'REG');
        $response->assertJsonPath('data.0.costs.0.cost.0.value', 10000);
    }

    public function test_api_id_shipping_cost_all_couriers()
    {
        // Mock API ID response
        Http::fake([
            'https://use.api.co.id/expedition/shipping-cost*' => Http::response([
                'data' => [
                    'couriers' => [
                        [
                            'courier_code' => 'jne',
                            'courier_name' => 'JNE Express',
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'price' => 10000,
                            'estimation' => '1-2 Days'
                        ],
                        [
                            'courier_code' => 'sicepat',
                            'courier_name' => 'SiCepat',
                            'service' => 'REG',
                            'description' => 'SiCepat Reg',
                            'price' => 11000,
                            'estimation' => '1-2 Days'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Set provider to api_id
        $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'api_id',
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id',
            'api_id_origin_id' => '1234567890', 
            'api_id_origin_label' => 'Test Village'
        ]);

        // Send courier 'all'
        $response = $this->post(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '0987654321',
            'weight' => 1000,
            'courier' => 'all'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        // Should return both JNE and SiCepat
        $data = $response->json('data');
        $this->assertCount(2, $data);
        $this->assertEquals('jne', $data[0]['code']);
        $this->assertEquals('sicepat', $data[1]['code']);
    }

    public function test_api_id_converts_grams_to_kg()
    {
        // Mock API ID response
        Http::fake([
            'https://use.api.co.id/expedition/shipping-cost*' => function (\Illuminate\Http\Client\Request $request) {
                // Verify that weight was converted to KG
                // Input was 1000 grams, so expected API payload weight is 1
                if ($request['weight'] == 1) {
                    return Http::response([
                        'data' => [
                            'couriers' => []
                        ]
                    ], 200);
                }
                
                return Http::response([], 400);
            }
        ]);

        // Set provider to api_id
        $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'api_id',
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id',
            'api_id_origin_id' => '1234567890', 
            'api_id_origin_label' => 'Test Village'
        ]);

        // Send 1000 grams
        $response = $this->post(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '0987654321',
            'weight' => 1000, // 1000 grams
            'courier' => 'jne'
        ]);

        $response->assertStatus(200);
    }

    public function test_api_id_converts_grams_to_kg_rounding_up()
    {
        // Mock API ID response
        Http::fake([
            'https://use.api.co.id/expedition/shipping-cost*' => function (\Illuminate\Http\Client\Request $request) {
                // Input 1200 grams -> 2 kg
                if ($request['weight'] == 2) {
                    return Http::response([
                        'data' => [
                            'couriers' => []
                        ]
                    ], 200);
                }
                return Http::response([], 400);
            }
        ]);

        // Set provider to api_id
        $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'api_id',
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id',
            'api_id_origin_id' => '1234567890', 
            'api_id_origin_label' => 'Test Village'
        ]);

        // Send 1200 grams
        $response = $this->post(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '0987654321',
            'weight' => 1200, // 1200 grams
            'courier' => 'jne'
        ]);

        $response->assertStatus(200);
    }

    public function test_shipping_cost_with_explicit_provider()
    {
        // Mock API ID response
        Http::fake([
            'https://use.api.co.id/expedition/shipping-cost*' => Http::response([
                'data' => [
                    'couriers' => [
                        [
                            'courier_code' => 'jne',
                            'courier_name' => 'JNE Express',
                            'service' => 'REG',
                            'description' => 'Layanan Reguler',
                            'price' => 12000,
                            'estimation' => '2-3 Days'
                        ]
                    ]
                ]
            ], 200)
        ]);

        // Set RajaOngkir as default in DB, but set keys for API ID
        $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'rajaongkir',
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id',
            'rajaongkir_origin_id' => '12345',
            'api_id_origin_id' => '9999999999' // Added explicit origin for API ID
        ]);

        // Request cost for API ID explicitly via provider param
        $response = $this->post(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '67890',
            'weight' => 1000,
            'courier' => 'jne',
            'provider' => 'api_id'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        // Should return API ID result (12000) not RajaOngkir result
        $response->assertJsonPath('data.0.costs.0.cost.0.value', 12000);
    }

    public function test_api_id_connection_test()
    {
        // Mock API response for shipping cost (expecting success)
        Http::fake([
            'https://use.api.co.id/expedition/shipping-cost*' => Http::response([
                'is_success' => true,
                'message' => 'Success',
                'data' => []
            ], 200)
        ]);
        
        // Ensure API ID settings are present
        $this->post(route('admin.integrations.update'), [
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id'
        ]);
        
        $response = $this->post(route('admin.integrations.test.api_id'));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);
    }

    public function test_search_destination_api_id()
    {
        // Mock API ID search response (Updated to match real API structure)
        Http::fake([
            'https://use.api.co.id/regional/indonesia/villages*' => Http::response([
                'data' => [
                    [
                        'code' => '1234567890',
                        'name' => 'Gambir',
                        'district_code' => '123',
                        'district' => 'Gambir',
                        'regency_code' => '12',
                        'regency' => 'Jakarta Pusat',
                        'province_code' => '1',
                        'province' => 'DKI Jakarta'
                    ]
                ]
            ], 200)
        ]);

        $this->post(route('admin.integrations.update'), [
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id'
        ]);

        $response = $this->get(route('admin.integrations.shipping.search', ['q' => 'Gambir', 'provider' => 'api_id']));

        $response->assertStatus(200);
        // ApiIdService maps 'code' -> 'subdistrict_id' and 'name' -> 'subdistrict_name' (Village + District)
        $response->assertJsonFragment([
            'subdistrict_id' => '1234567890',
            'subdistrict_name' => 'Gambir, Gambir',
            'city_name' => 'Jakarta Pusat',
            'province_name' => 'DKI Jakarta'
        ]);
    }

    public function test_search_destination_api_id_error_handling()
    {
        // Mock API ID search response with error
        Http::fake([
            'https://use.api.co.id/regional/indonesia/villages*' => Http::response(null, 500)
        ]);

        $this->post(route('admin.integrations.update'), [
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id'
        ]);

        // Should handle error gracefully and return empty array
        $response = $this->get(route('admin.integrations.shipping.search', ['q' => 'ErrorCase', 'provider' => 'api_id']));

        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_search_destination_api_id_fallback_to_district()
    {
        Http::fake([
            // Village search returns empty
            'https://use.api.co.id/regional/indonesia/villages?name=Coblong' => Http::response([
                'data' => []
            ], 200),
            
            // District search returns Coblong
            'https://use.api.co.id/regional/indonesia/districts?name=Coblong' => Http::response([
                'data' => [
                    [
                        'code' => '3273060',
                        'name' => 'Coblong',
                        'regency_code' => '3273',
                        'regency' => 'Kota Bandung',
                        'province_code' => '32',
                        'province' => 'Jawa Barat'
                    ]
                ]
            ], 200),

            // Fetch villages for District Coblong
            'https://use.api.co.id/regional/indonesia/villages?district_code=3273060' => Http::response([
                'data' => [
                    [
                        'code' => '3273060001',
                        'name' => 'Cipaganti',
                        'district_code' => '3273060',
                        'district' => 'Coblong',
                        'regency_code' => '3273',
                        'regency' => 'Kota Bandung',
                        'province_code' => '32',
                        'province' => 'Jawa Barat'
                    ]
                ]
            ], 200)
        ]);

        $this->post(route('admin.integrations.update'), [
            'api_id_key' => 'mock-key',
            'api_id_base_url' => 'https://use.api.co.id'
        ]);

        $response = $this->get(route('admin.integrations.shipping.search', ['q' => 'Coblong', 'provider' => 'api_id']));

        $response->assertStatus(200);
        // Should find Cipaganti village via Coblong district search
        $response->assertJsonFragment([
            'subdistrict_name' => 'Cipaganti, Coblong'
        ]);
    }

    public function test_search_destination_short_query()
    {
        $response = $this->get(route('admin.integrations.shipping.search', ['q' => 'ab']));
        $response->assertStatus(200);
        $response->assertJson([]);
    }

    public function test_rajaongkir_shipping_cost_all_couriers()
    {
        // Mock RajaOngkir response for multiple calls
        Http::fake([
            'https://api.rajaongkir.com/starter/cost' => function ($request) {
                $courier = $request['courier'];
                
                if ($courier === 'jne') {
                    return Http::response([
                        'rajaongkir' => [
                            'results' => [
                                [
                                    'code' => 'jne',
                                    'name' => 'JNE',
                                    'costs' => []
                                ]
                            ]
                        ]
                    ], 200);
                }
                
                if ($courier === 'pos') {
                    return Http::response([
                        'rajaongkir' => [
                            'results' => [
                                [
                                    'code' => 'pos',
                                    'name' => 'POS Indonesia',
                                    'costs' => []
                                ]
                            ]
                        ]
                    ], 200);
                }
                
                // Return empty for others
                return Http::response(['rajaongkir' => ['results' => []]], 200);
            }
        ]);

        // Set provider to rajaongkir
        $this->post(route('admin.integrations.update'), [
            'shipping_provider' => 'rajaongkir',
            'rajaongkir_api_key' => 'mock-key',
            'rajaongkir_base_url' => 'https://api.rajaongkir.com/starter',
            'rajaongkir_origin_id' => '123'
        ]);

        // Send courier 'all'
        $response = $this->post(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '456',
            'weight' => 1000,
            'courier' => 'all'
        ]);

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
        
        $data = $response->json('data');
        // We expect JNE and POS
        $codes = array_column($data, 'code');
        $this->assertContains('jne', $codes);
        $this->assertContains('pos', $codes);
    }

    public function test_store_shipping_configuration()
    {
        // 1. Create a Store
        $user = User::factory()->create();
        $store = \App\Models\Store::create([
            'user_id' => $user->id,
            'name' => 'Test Store',
            'slug' => 'test-store',
            'shipping_couriers' => []
        ]);

        // 2. Set Global Active Couriers
        $this->post(route('admin.integrations.update'), [
            'shipping_active_couriers' => ['jne', 'pos']
        ]);

        // 3. Update Store with Valid Couriers
        $response = $this->post(route('admin.integrations.shipping.store-update'), [
            'store_id' => $store->id,
            'couriers' => ['jne']
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(['jne'], $store->fresh()->shipping_couriers);

        // 4. Update Store with Invalid Courier (not in global)
        $response = $this->post(route('admin.integrations.shipping.store-update'), [
            'store_id' => $store->id,
            'couriers' => ['jne', 'tiki'] // tiki is not active globally
        ]);
        
        // It should silently filter out invalid ones
        $this->assertEquals(['jne'], $store->fresh()->shipping_couriers);
    }
}
