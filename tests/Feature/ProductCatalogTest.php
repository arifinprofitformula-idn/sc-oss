<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProductCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create roles
        Role::create(['name' => 'SUPER_ADMIN']);
        Role::create(['name' => 'SILVERCHANNEL']);

        // Create admin user
        $this->admin = User::factory()->create(['status' => 'ACTIVE']);
        $this->admin->assignRole('SUPER_ADMIN');
    }

    public function test_admin_can_access_catalog_pages()
    {
        $response = $this->actingAs($this->admin)->get(route('admin.brands.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('admin.categories.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->get(route('admin.products.index'));
        $response->assertStatus(200);
    }

    public function test_admin_can_create_brand()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.brands.store'), [
            'name' => 'Test Brand',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.brands.index'));
        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']);
    }

    public function test_admin_can_create_category()
    {
        $response = $this->actingAs($this->admin)->post(route('admin.categories.store'), [
            'name' => 'Test Category',
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.categories.index'));
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    public function test_admin_can_create_product()
    {
        $brand = Brand::create(['name' => 'Brand A', 'slug' => 'brand-a']);
        $category = Category::create(['name' => 'Category A', 'slug' => 'category-a']);

        $response = $this->actingAs($this->admin)->post(route('admin.products.store'), [
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'sku' => 'SKU-001',
            'price_silverchannel' => 100000,
            'price_msrp' => 150000,
            'stock' => 50,
            'is_active' => 1,
        ]);

        $response->assertRedirect(route('admin.products.index'));
        $this->assertDatabaseHas('products', [
            'name' => 'Test Product',
            'sku' => 'SKU-001',
            'price_silverchannel' => 100000,
        ]);
    }

    public function test_unauthorized_user_cannot_access_catalog()
    {
        $user = User::factory()->create(['status' => 'ACTIVE']);
        $user->assignRole('SILVERCHANNEL');

        $response = $this->actingAs($user)->get(route('admin.products.index'));
        $response->assertStatus(403); // Spatie middleware returns 403
    }
}
