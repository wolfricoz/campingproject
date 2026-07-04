<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Location;
use App\Models\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
 */
class ArrangementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // start_date must fall within the current month
        $startDate = $this->faker->dateTimeBetween(now()->startOfMonth(), now()->endOfMonth());

        // Stay length is 1 to 10 days, end_date can spill into next month
        $endDate = (clone $startDate)->modify('+' . $this->faker->numberBetween(1, 10) . ' days');

        return [
            'customer_id' => Customer::factory(),
            'location_id' => Location::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_price' => $this->faker->randomFloat(2, 50, 2000),
            'booking_status' => $this->faker->randomElement(['pending', 'confirmed', 'cancelled']),
            'source' => $this->faker->randomElement(['website', 'phone', 'walk-in', 'agency']),
            'confirmation_email_sent' => $this->faker->boolean(),
            'payment_received' => $this->faker->boolean(),
            'status' => 1,
        ];
    }
}
