<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

class RajaOngkirTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed roles
        $role = Role::firstOrCreate(['name' => 'super_admin']);
        $user = User::factory()->create();
        $user->assignRole($role);
        
        $this->actingAs($user);

        // Setup Integration settings
        SystemSetting::create(['key' => 'rajaongkir_api_key', 'value' => 'test_key', 'group' => 'rajaongkir', 'type' => 'text']);
        SystemSetting::create(['key' => 'rajaongkir_base_url', 'value' => 'https://api.rajaongkir.com/starter', 'group' => 'rajaongkir', 'type' => 'text']);
        SystemSetting::create(['key' => 'rajaongkir_origin_id', 'value' => '501', 'group' => 'rajaongkir', 'type' => 'text']); // Yogyakarta
    }

    public function test_admin_can_view_rajaongkir_settings()
    {
        $response = $this->get(route('admin.integrations.shipping'));
        $response->assertStatus(200);
        $response->assertSee('RajaOngkir Settings');
    }

    public function test_calculate_shipping_cost_successfully()
    {
        // Mock RajaOngkir Response
        Http::fake([
            'api.rajaongkir.com/*' => Http::response([
                'rajaongkir' => [
                    'status' => ['code' => 200, 'description' => 'OK'],
                    'results' => [
                        [
                            'code' => 'jne',
                            'name' => 'Jalur Nugraha Ekakurir (JNE)',
                            'costs' => [
                                [
                                    'service' => 'OKE',
                                    'description' => 'Ongkos Kirim Ekonomis',
                                    'cost' => [['value' => 38000, 'etd' => '4-5', 'note' => '']]
                                ]
                            ]
                        ]
                    ]
                ]
            ], 200),
        ]);

        $response = $this->postJson(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '114', // Denpasar
            'weight' => 1000,
            'courier' => 'jne'
        ]);

        $response->assertStatus(200)
                 ->assertJson([
                     'success' => true,
                     'data' => [
                         [
                             'code' => 'jne',
                             'costs' => [
                                 [
                                     'service' => 'OKE',
                                     'cost' => [['value' => 38000]]
                                 ]
                             ]
                         ]
                     ]
                 ]);
    }

    public function test_calculate_shipping_cost_validation_error()
    {
        $response = $this->postJson(route('admin.integrations.shipping.test-cost'), [
            'destination_id' => '', // Empty
            'weight' => '',
            'courier' => ''
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['destination_id', 'weight', 'courier']);
    }

    public function test_calculate_shipping_cost_api_error_handling()
    {
        // Mock API Failure
        Http::fake([
            'api.rajaongkir.com/*' => Http::response([
                'rajaongkir' => [
                    'status' => ['code' => 400, 'description' => 'Invalid Key'],
                    'results' => []
                ]
            ], 400),
        ]);

        $response = $this->postJson(route('admin.integrations.rajaongkir.test-cost'), [
            'destination_id' => '114',
            'weight' => 1000,
            'courier' => 'jne'
        ]);

        $response->assertStatus(500)
                 ->assertJson([
                     'success' => false,
                     'message' => 'Invalid Key'
                 ]);
    }
}
