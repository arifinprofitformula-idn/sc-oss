<?php

namespace Tests\Feature\Admin;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCommissionApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $brand;
    protected $category;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::firstOrCreate(['name' => 'SUPER_ADMIN', 'guard_name' => 'web']);

        // Create Admin
        $this->admin = User::factory()->create();
        $this->admin->assignRole('SUPER_ADMIN');

        // Create Dependencies
        $this->brand = Brand::factory()->create();
        $this->category = Category::factory()->create();

        Storage::fake('public');
    }

    public function test_admin_can_create_product_with_commission_api()
    {
        $response = $this->actingAs($this->admin)->postJson(route('api.admin.products.store'), [
            'name' => 'Commission Product',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'sku' => 'COM-001',
            'price_customer' => 150000,
            'price_silverchannel' => 120000,
            'stock' => 100,
            'weight' => 500,
            'is_active' => true,
            // Commission
            'commission_enabled' => true,
            'commission_type' => 'percentage',
            'commission_value' => 10,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'name' => 'Commission Product',
                    'commission_enabled' => true,
                    'commission_type' => 'percentage',
                    'commission_value' => 10.00,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'sku' => 'COM-001',
            'commission_enabled' => 1,
            'commission_type' => 'percentage',
            'commission_value' => 10.00,
        ]);
    }

    public function test_admin_can_update_product_commission_api()
    {
        $product = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'commission_enabled' => false,
            'commission_type' => 'percentage',
            'commission_value' => 0,
        ]);

        $response = $this->actingAs($this->admin)->putJson(route('api.admin.products.update', $product->id), [
            'commission_enabled' => true,
            'commission_type' => 'fixed',
            'commission_value' => 5000,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'commission_enabled' => true,
                    'commission_type' => 'fixed',
                    'commission_value' => 5000.00,
                ]
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'commission_enabled' => 1,
            'commission_type' => 'fixed',
            'commission_value' => 5000.00,
        ]);
    }

    public function test_admin_can_get_product_commission_details_api()
    {
        $product = Product::factory()->create([
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'commission_enabled' => true,
            'commission_type' => 'percentage',
            'commission_value' => 15,
        ]);

        $response = $this->actingAs($this->admin)->getJson(route('api.admin.products.commission', $product->id));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $product->id,
                    'commission_enabled' => true,
                    'commission_type' => 'percentage',
                    'commission_value' => 15.00,
                ]
            ]);
    }

    public function test_validation_errors_for_commission_fields_api()
    {
        $response = $this->actingAs($this->admin)->postJson(route('api.admin.products.store'), [
            'name' => 'Invalid Commission Product',
            'brand_id' => $this->brand->id,
            'category_id' => $this->category->id,
            'sku' => 'COM-INV-001',
            'price_customer' => 100000,
            'price_silverchannel' => 90000,
            'stock' => 10,
            // Invalid Commission
            'commission_enabled' => true,
            'commission_type' => 'invalid',
            'commission_value' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['commission_type', 'commission_value']);
    }
}
