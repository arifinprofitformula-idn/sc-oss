<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(3, true);
        return [
            'brand_id' => Brand::factory(),
            'category_id' => Category::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => strtoupper($this->faker->unique()->bothify('PROD-#####')),
            'description' => $this->faker->sentence,
            'price_msrp' => $this->faker->numberBetween(100000, 500000),
            'price_silverchannel' => $this->faker->numberBetween(80000, 400000),
            'weight' => $this->faker->numberBetween(100, 2000),
            'stock' => $this->faker->numberBetween(0, 100),
            'image' => null,
            'is_active' => true,
        ];
    }
}
