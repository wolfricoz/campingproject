<?php

namespace Tests\Feature;

use App\Models\Location;
use Database\Seeders\LocationsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class LocationsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_at_least_five_locations(): void
    {
        $this->seed(LocationsSeeder::class);

        $this->assertGreaterThanOrEqual(5, Location::count());
        $this->assertSame(Location::count(), Location::where('status', 1)->count());
    }

    public function test_every_seeded_location_is_complete(): void
    {
        $this->seed(LocationsSeeder::class);

        foreach (Location::all() as $location) {
            $this->assertTrue(Str::isUuid($location->guid), 'Locatie kreeg geen geldige uuid als guid.');
            $this->assertNotEmpty($location->name);
            $this->assertNotEmpty($location->type);
            $this->assertNotEmpty($location->description);
            $this->assertGreaterThan(0, $location->capacity);
            $this->assertGreaterThan(0, (float) $location->price_per_night);
        }
    }

    /**
     * De faker-namen ("voluptatum ducimus dolores") zagen er in een demo niet uit; elke
     * locatie hoort een naam te hebben die op de camping thuishoort.
     */
    public function test_no_location_has_a_lorem_ipsum_name(): void
    {
        $this->seed(LocationsSeeder::class);

        $prefixes = ['Kampeerveld', 'Kampeerplaats', 'Caravanplaats', 'Chalet', 'Camperplaats', 'Trekkershut'];

        foreach (Location::pluck('name') as $name) {
            $this->assertTrue(
                Str::startsWith($name, $prefixes),
                "De locatienaam '{$name}' begint niet met een herkenbaar soort plaats.",
            );
        }
    }

    public function test_the_advertised_locations_are_shown_with_a_photo(): void
    {
        $this->seed(LocationsSeeder::class);

        $advertised = Location::where('is_advertised', true)->get();

        $this->assertGreaterThanOrEqual(4, $advertised->count(), 'De homepage toont vier uitgelichte locaties.');

        foreach ($advertised as $location) {
            $this->assertNotEmpty($location->photo, "Uitgelichte locatie '{$location->name}' heeft geen foto.");
        }
    }

    /**
     * Ook de losse factory mag geen lorem meer opleveren, want die vult de lijsten aan.
     */
    public function test_the_factory_generates_a_recognisable_name(): void
    {
        $location = Location::factory()->create();

        $this->assertMatchesRegularExpression(
            '/^(Kampeerplaats|Caravanplaats|Chalet|Camperplaats) [A-Z]\d{1,2}$/',
            $location->name,
        );
        $this->assertFalse((bool) $location->is_advertised);
        $this->assertTrue((bool) Location::factory()->advertised()->create()->is_advertised);
    }
}
