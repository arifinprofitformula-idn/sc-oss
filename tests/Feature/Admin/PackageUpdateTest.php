<?php

namespace Tests\Feature\Admin;

use App\Models\Package;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PackageUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Setup admin user
        $role = Role::firstOrCreate(['name' => 'SUPER_ADMIN']);
        $this->admin = User::factory()->create();
        $this->admin->assignRole($role);
    }

    public function test_can_update_package_with_bundled_products()
    {
        // 1. Create a package
        $package = Package::factory()->create([
            'name' => 'Original Package',
        ]);

        // 2. Create products
        $product1 = Product::factory()->create(['name' => 'Product 1']);
        $product2 = Product::factory()->create(['name' => 'Product 2']);

        // 3. Prepare update data
        $data = [
            'name' => 'Updated Package',
            'price' => 150000,
            'weight' => 500,
            'duration_days' => 30,
            'commission_type' => 'fixed',
            'commission_value' => 50000,
            'is_active' => 1,
            'products' => [
                [
                    'id' => $product1->id,
                    'quantity' => 2,
                ],
                [
                    'id' => $product2->id,
                    'quantity' => 1,
                ],
            ],
        ];

        // 4. Send PUT request (simulated)
        // Note: In actual browser, it's POST with _method=PUT and FormData
        // We use actingAs to bypass auth
        $response = $this->actingAs($this->admin)
            ->put(route('admin.packages.update', $package), $data);

        // 5. Assertions
        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('admin.packages.index'));

        // Check Package Update
        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'name' => 'Updated Package',
        ]);

        // Check Products Attached
        $this->assertDatabaseHas('package_products', [
            'package_id' => $package->id,
            'product_id' => $product1->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('package_products', [
            'package_id' => $package->id,
            'product_id' => $product2->id,
            'quantity' => 1,
        ]);
    }

    public function test_can_update_package_with_bundled_products_via_ajax_multipart()
    {
        // This simulates the JS FormData request more closely
        $package = Package::factory()->create();
        $product1 = Product::factory()->create();

        $data = [
            '_method' => 'PUT', // Spoof PUT
            'name' => 'Updated Via Ajax',
            'price' => 200000,
            'weight' => 100,
            'duration_days' => 15,
            'commission_type' => 'percentage',
            'commission_value' => 10,
            'is_active' => 1,
            'products' => [
                0 => ['id' => $product1->id, 'quantity' => 5]
            ]
        ];

        $response = $this->actingAs($this->admin)
            ->postJson(route('admin.packages.update', $package), $data);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Paket berhasil diperbarui.']);

        $this->assertDatabaseHas('package_products', [
            'package_id' => $package->id,
            'product_id' => $product1->id,
            'quantity' => 5,
        ]);
    }
}
