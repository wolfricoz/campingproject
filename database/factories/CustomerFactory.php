<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $dutchFaker = fake('nl_NL');

        return [
            'guid' => $this->faker->uuid(),
            'name' => $dutchFaker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_number' => '06-'.$this->faker->numerify('########'),
            'street_name' => $dutchFaker->streetName(),
            'street_number' => (string) $this->faker->numberBetween(1, 180),
            'postal_code' => $this->faker->numberBetween(1011, 9999).' '.$this->faker->regexify('[A-Z]{2}'),
            'city' => $dutchFaker->city(),
            'country' => $this->faker->randomElement(['Nederland', 'Nederland', 'Nederland', 'België', 'Duitsland']),
            'user_id' => null,
            'status' => 1,
        ];
    }
}
