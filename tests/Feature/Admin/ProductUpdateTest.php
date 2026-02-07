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

class ProductUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::create(['name' => 'SUPER_ADMIN']);
    }

    public function test_admin_can_view_edit_page()
    {
        $user = User::factory()->create();
        $user->assignRole('SUPER_ADMIN');

        $product = Product::factory()->create();

        $response = $this->actingAs($user)->get(route('admin.products.edit', $product));

        $response->assertStatus(200);
        $response->assertViewIs('admin.products.edit');
        $response->assertViewHas('product');
    }

    public function test_admin_can_update_product()
    {
        $user = User::factory()->create();
        $user->assignRole('SUPER_ADMIN');

        $brand = Brand::factory()->create();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'stock' => 10
        ]);

        Storage::fake('public');
        $image = UploadedFile::fake()->image('new-product.jpg');

        $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Updated Name',
            'sku' => 'UPD-001',
            'description' => 'Updated Description',
            'price_silverchannel' => 150000,
            'price_msrp' => 200000,
            'weight' => 500,
            'stock' => 20, // Change stock
            'image' => $image,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'name' => 'Updated Name',
            'sku' => 'UPD-001',
            'stock' => 20,
        ]);

        // Check stock log
        $this->assertDatabaseHas('product_stock_logs', [
            'product_id' => $product->id,
            'type' => 'manual_adjustment',
            'quantity' => 10, // 20 - 10
            'final_stock' => 20,
        ]);
    }

    public function test_update_validation()
    {
        $user = User::factory()->create();
        $user->assignRole('SUPER_ADMIN');

        $product = Product::factory()->create();

        $response = $this->actingAs($user)->put(route('admin.products.update', $product), [
            'name' => '', // Required
        ]);

        $response->assertSessionHasErrors(['name']);
    }
}
