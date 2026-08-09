<?php

namespace Database\Factories;

use App\Models\Location;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    /**
     * @var array<string, array{prefix: string, capacity: array{int, int}, bedrooms: array{int, int}, size: array{int, int}, price: array{int, int}}>
     */
    private const TYPES = [
        'tent pitch' => [
            'prefix' => 'Kampeerplaats',
            'capacity' => [2, 6],
            'bedrooms' => [1, 1],
            'size' => [80, 140],
            'price' => [18, 30],
        ],
        'caravan pitch' => [
            'prefix' => 'Caravanplaats',
            'capacity' => [2, 6],
            'bedrooms' => [1, 1],
            'size' => [100, 160],
            'price' => [25, 42],
        ],
        'cabin' => [
            'prefix' => 'Chalet',
            'capacity' => [4, 8],
            'bedrooms' => [2, 4],
            'size' => [30, 70],
            'price' => [80, 145],
        ],
        'RV spot' => [
            'prefix' => 'Camperplaats',
            'capacity' => [2, 4],
            'bedrooms' => [1, 1],
            'size' => [60, 100],
            'price' => [28, 48],
        ],
    ];

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = $this->faker->randomElement(array_keys(self::TYPES));
        $settings = self::TYPES[$type];

        return [
            'guid' => $this->faker->uuid(),
            'name' => $settings['prefix'].' '.strtoupper($this->faker->randomLetter()).$this->faker->numberBetween(1, 60),
            'type' => $type,
            'description' => 'Een '.strtolower($settings['prefix']).' op het rustige gedeelte van de camping, '
                .'op loopafstand van het sanitairgebouw en de speelweide.',
            'photo' => null,

            'capacity' => $this->faker->numberBetween(...$settings['capacity']),
            'bedrooms' => $this->faker->numberBetween(...$settings['bedrooms']),
            'size' => $this->faker->numberBetween(...$settings['size']),
            'price_per_night' => $this->faker->numberBetween(...$settings['price']) + 0.5,

            'has_electricity' => $this->faker->boolean(80),
            'has_water' => $this->faker->boolean(70),
            'has_shade' => $this->faker->boolean(),

            'status' => 1,
            'is_advertised' => false,
        ];
    }

    public function advertised(): static
    {
        return $this->state(fn (array $attributes): array => [
            'is_advertised' => true,
            'photo' => '/images/header.jpg',
        ]);
    }
}
