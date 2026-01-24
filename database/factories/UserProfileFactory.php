<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserProfile>
 */
class UserProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'birth_place' => $this->faker->city,
            'birth_date' => $this->faker->date,
            'gender' => $this->faker->randomElement(['L', 'P']),
            'religion' => 'Islam',
            'marital_status' => 'Single',
            'job' => $this->faker->jobTitle,
        ];
    }
}
