<?php

namespace Database\Factories;

use App\Models\News;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<News>
 */
class NewsFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $title = Str::limit(rtrim($this->faker->sentence(6), '.'), 100, '');

        return [
            'guid' => $this->faker->uuid(),
            'title' => $title,
            'slug' => Str::slug($title),
            'summary' => $this->faker->sentence(15),
            'content' => $this->faker->paragraphs(4, true),
            'image' => 'images/header.jpg',
            'type' => $this->faker->randomElement(['Algemeen', 'Evenement', 'Onderhoud', 'Aanbieding']),
            'published' => true,
            'status' => 1,
        ];
    }

    /**
     * A draft article that is not visible on the public news page.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes): array => [
            'published' => false,
        ]);
    }
}
