<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Package>
 */
class PackageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'image' => null,
            'description' => $this->faker->sentence(),
            'price' => 500000,
            'weight' => 1000,
            'original_price' => 750000,
            'duration_days' => 30,
            'benefits' => ['Benefit 1', 'Benefit 2'],
            'is_active' => true,
            'commission_type' => 'fixed',
            'commission_value' => 50000,
        ];
    }
}
