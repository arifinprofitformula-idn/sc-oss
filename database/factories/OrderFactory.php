<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class OrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => 'ORD-' . strtoupper(Str::random(10)),
            'order_key' => 'wc_order_' . Str::random(13),
            'total_amount' => 100000,
            'subtotal' => 90000,
            'tax_amount' => 0,
            'shipping_cost' => 10000,
            'insurance_amount' => 0,
            'status' => 'WAITING_PAYMENT',
            'shipping_address' => 'Test Address',
            'shipping_courier' => 'jne',
            'shipping_service' => 'REG',
            'payment_method' => 'transfer',
        ];
    }
}
