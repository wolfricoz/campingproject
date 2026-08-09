<?php

namespace Tests\Feature;

use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalculationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_days_endpoint_returns_the_amount_of_nights_for_a_guest(): void
    {
        $response = $this->getJson(route('api.calculations.days', [
            'start_date' => '2026-08-01T14:00',
            'end_date' => '2026-08-01T18:00',
        ]));

        $response->assertOk()->assertJson(['days' => 1]);
    }

    public function test_days_endpoint_requires_both_dates(): void
    {
        $response = $this->getJson(route('api.calculations.days', ['start_date' => '2026-08-01T14:00']));

        $response->assertStatus(422)->assertJsonValidationErrors('end_date');
    }

    public function test_days_endpoint_rejects_an_end_date_before_the_start_date(): void
    {
        $response = $this->getJson(route('api.calculations.days', [
            'start_date' => '2026-08-05T14:00',
            'end_date' => '2026-08-01T14:00',
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('end_date');
    }

    public function test_price_endpoint_multiplies_the_nightly_rate_by_the_amount_of_nights(): void
    {
        $location = Location::factory()->create(['price_per_night' => 45.50]);

        $response = $this->getJson(route('api.calculations.price', [
            'location_id' => $location->id,
            'days' => 3,
        ]));

        $response->assertOk()->assertJson(['price' => 136.5]);
    }

    public function test_price_endpoint_rejects_an_unknown_location(): void
    {
        $response = $this->getJson(route('api.calculations.price', [
            'location_id' => 999999,
            'days' => 3,
        ]));

        $response->assertStatus(422)->assertJsonValidationErrors('location_id');
    }
}
