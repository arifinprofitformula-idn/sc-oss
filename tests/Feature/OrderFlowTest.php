<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderFlowTest extends TestCase
{
    use RefreshDatabase;

    protected $silverchannel;
    protected $admin;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Roles
        $roleSc = Role::create(['name' => 'SILVERCHANNEL']);
        $roleAdmin = Role::create(['name' => 'SUPER_ADMIN']);

        // Create Users
        $this->silverchannel = User::factory()->create([
            'status' => 'ACTIVE',
            'address' => 'Test Address 123'
        ]);
        $this->silverchannel->assignRole('SILVERCHANNEL');

        $this->admin = User::factory()->create(['status' => 'ACTIVE']);
        $this->admin->assignRole('SUPER_ADMIN');

        // Create Product
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);

        $this->product = Product::create([
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TEST-001',
            'price_msrp' => 100000,
            'price_silverchannel' => 90000,
            'stock' => 100,
            'is_active' => true,
        ]);
    }

    public function test_silverchannel_can_add_product_to_cart()
    {
        $response = $this->actingAs($this->silverchannel)
                         ->post(route('silverchannel.cart.store'), [
                             'product_id' => $this->product->id,
                             'quantity' => 2
                         ]);

        $response->assertRedirect(route('silverchannel.cart.index'));
        $this->assertDatabaseHas('carts', [
            'user_id' => $this->silverchannel->id,
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);
    }

    public function test_silverchannel_can_create_order_from_cart()
    {
        // Add to cart first
        $this->silverchannel->cart()->create([
            'product_id' => $this->product->id,
            'quantity' => 2
        ]);

        $response = $this->actingAs($this->silverchannel)
                         ->post(route('silverchannel.orders.store'), [
                             'shipping_address' => 'New Shipping Address',
                             'notes' => 'Please pack carefully'
                         ]);

        $order = Order::where('user_id', $this->silverchannel->id)->first();

        $response->assertRedirect(route('silverchannel.orders.show', $order));
        
        $this->assertNotNull($order);
        $this->assertEquals('DRAFT', $order->status);
        $this->assertEquals(180000, $order->total_amount); // 90000 * 2
        $this->assertEquals('New Shipping Address', $order->shipping_address);
        
        // Cart should be empty
        $this->assertDatabaseCount('carts', 0);
        
        // Stock should be deducted
        $this->assertEquals(98, $this->product->fresh()->stock);
    }

    public function test_admin_can_update_order_status()
    {
        $order = Order::create([
            'order_number' => 'ORD-TEST-001',
            'user_id' => $this->silverchannel->id,
            'total_amount' => 180000,
            'status' => 'DRAFT',
            'shipping_address' => 'Test Address'
        ]);

        $response = $this->actingAs($this->admin)
                         ->post(route('admin.orders.update-status', $order), [
                             'status' => 'PAID',
                             'note' => 'Payment received'
                         ]);

        $response->assertRedirect(route('admin.orders.show', $order));
        
        $this->assertEquals('PAID', $order->fresh()->status);
        
        $this->assertDatabaseHas('order_logs', [
            'order_id' => $order->id,
            'to_status' => 'PAID',
            'note' => 'Payment received'
        ]);
    }

    public function test_silverchannel_cannot_access_admin_orders()
    {
        $response = $this->actingAs($this->silverchannel)
                         ->get(route('admin.orders.index'));

        $response->assertForbidden();
    }
}
