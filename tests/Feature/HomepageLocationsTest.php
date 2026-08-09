<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/**
 * De homepage toont alleen de plaatsen die als uitgelicht zijn aangevinkt, de
 * locatiepagina toont ze allemaal.
 */
class HomepageLocationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_homepage_only_shows_advertised_locations(): void
    {
        Location::factory()->advertised()->create(['name' => 'Chalet Boslust']);
        Location::factory()->create(['name' => 'Chalet Verstopt']);

        $response = $this->get(route('home'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Welcome')
                ->has('locations', 1)
                ->where('locations.0.name', 'Chalet Boslust')
        );
    }

    public function test_an_inactive_location_stays_off_the_homepage(): void
    {
        Location::factory()->advertised()->create(['status' => 0]);

        $response = $this->get(route('home'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('locations', 0)
        );
    }

    public function test_the_homepage_shows_at_most_four_locations(): void
    {
        Location::factory(6)->advertised()->create();

        $response = $this->get(route('home'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page->has('locations', 4)
        );
    }

    public function test_the_locations_page_shows_every_active_location(): void
    {
        Location::factory()->advertised()->create();
        Location::factory(2)->create();
        Location::factory()->create(['status' => 0]);

        $response = $this->get(route('locations'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Locations/Index')
                ->has('locations', 3)
        );
    }
}
