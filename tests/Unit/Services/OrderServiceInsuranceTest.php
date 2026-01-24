<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderServiceInsuranceTest extends TestCase
{
    use RefreshDatabase;

    protected $orderService;
    protected $user;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->orderService = app(OrderService::class);
        $this->user = User::factory()->create();
        $this->product = Product::factory()->create([
            'price_silverchannel' => 100000,
            'weight' => 1000,
            'stock' => 10
        ]);
    }

    public function test_create_order_with_insurance_calculation()
    {
        $items = [
            [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'price' => 100000,
                'total' => 100000,
                'weight' => 1000
            ]
        ];

        $addressData = [
            'address' => 'Test Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'notes' => 'Test Notes',
            'subdistrict_name' => 'Test Subdistrict',
            'city_name' => 'Test City',
            'province_name' => 'Test Province',
            'phone' => '08123456789'
        ];

        $shippingData = [
            'courier' => 'jne',
            'service' => 'REG',
            'cost' => 10000,
            'etd' => '1-2 Days'
        ];

        $subtotal = 100000;
        $shippingCost = 10000;
        $insuranceAmount = 5000; // Example insurance amount
        $uniqueCode = 123;
        $taxAmount = 0;

        $order = $this->orderService->createOrder(
            $this->user,
            $items,
            $addressData,
            $shippingData,
            'transfer',
            $subtotal,
            $shippingCost,
            $taxAmount,
            'Test Notes',
            $uniqueCode,
            null,
            $insuranceAmount
        );

        // Assertions
        $this->assertInstanceOf(Order::class, $order);
        $this->assertEquals($insuranceAmount, $order->insurance_amount);
        
        // Total = Subtotal + Shipping + Tax + Insurance + UniqueCode
        // 100000 + 10000 + 0 + 5000 + 123 = 115123
        $expectedTotal = $subtotal + $shippingCost + $taxAmount + $insuranceAmount + $uniqueCode;
        $this->assertEquals($expectedTotal, $order->total_amount);
    }

    public function test_create_order_without_insurance()
    {
        $items = [
            [
                'product_id' => $this->product->id,
                'quantity' => 1,
                'price' => 100000,
                'total' => 100000,
                'weight' => 1000
            ]
        ];

        $addressData = [
            'address' => 'Test Address',
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'notes' => 'Test Notes',
            'subdistrict_name' => 'Test Subdistrict',
            'city_name' => 'Test City',
            'province_name' => 'Test Province',
            'phone' => '08123456789'
        ];

        $shippingData = [
            'courier' => 'jne',
            'service' => 'REG',
            'cost' => 10000,
            'etd' => '1-2 Days'
        ];

        $subtotal = 100000;
        $shippingCost = 10000;
        $insuranceAmount = 0;
        $uniqueCode = 123;

        $order = $this->orderService->createOrder(
            $this->user,
            $items,
            $addressData,
            $shippingData,
            'transfer',
            $subtotal,
            $shippingCost,
            0,
            null,
            $uniqueCode,
            null,
            $insuranceAmount
        );

        $this->assertEquals(0, $order->insurance_amount);
        $this->assertEquals(110123, $order->total_amount);
    }
}
