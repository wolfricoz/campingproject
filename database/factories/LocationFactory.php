<?php

namespace Database\Factories;

use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class LocationFactory extends Factory
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
            'type' => $this->faker->randomElement(['tent pitch', 'caravan pitch', 'cabin', 'RV spot']),
            'description' => $this->faker->paragraph(),

            'capacity' => $this->faker->numberBetween(1, 8),
            'bedrooms' => $this->faker->numberBetween(1, 4),
            'size' => $this->faker->randomFloat(2, 10, 200),
            'price_per_night' => $this->faker->randomFloat(2, 20, 500),

            'has_electricity' => $this->faker->boolean(),
            'has_water' => $this->faker->boolean(),
            'has_shade' => $this->faker->boolean(),

            'status' => 1,
        ];
    }
}
