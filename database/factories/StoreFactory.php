<?php

namespace Database\Factories;

use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Store>
 */
class StoreFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->company(),
            'slug' => $this->faker->slug(),
            'description' => $this->faker->sentence(),
            'logo_path' => null,
            'address' => $this->faker->address(),
            'province_id' => 1,
            'city_id' => 1,
            'subdistrict_id' => 1,
            'postal_code' => '12345',
            'phone' => $this->faker->phoneNumber(),
            'whatsapp' => $this->faker->phoneNumber(),
            'email' => $this->faker->companyEmail(),
            'operating_hours' => null,
            'is_open' => true,
            'holiday_mode' => false,
            'notification_templates' => null,
        ];
    }
}
