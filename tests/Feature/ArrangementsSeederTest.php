<?php

namespace Tests\Feature;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use App\Models\Location;
use Database\Seeders\ArrangementsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArrangementsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_bookings_for_every_location(): void
    {
        $this->seed(ArrangementsSeeder::class);

        $this->assertGreaterThan(0, Arrangement::count());

        foreach (Location::all() as $location) {
            $this->assertGreaterThan(
                0,
                $location->arrangements()->count(),
                "Locatie '{$location->name}' heeft geen enkele boeking gekregen.",
            );
        }
    }

    public function test_every_booking_has_a_customer_a_period_and_a_price(): void
    {
        $this->seed(ArrangementsSeeder::class);

        foreach (Arrangement::with(['customer', 'location'])->get() as $arrangement) {
            $this->assertNotNull($arrangement->customer, 'Boeking zonder klant.');
            $this->assertNotNull($arrangement->location, 'Boeking zonder locatie.');
            $this->assertTrue($arrangement->end_date->greaterThan($arrangement->start_date));
            $this->assertGreaterThan(0, (float) $arrangement->total_price);
        }
    }

    public function test_no_location_is_double_booked(): void
    {
        $this->seed(ArrangementsSeeder::class);

        $blocking = Arrangement::query()
            ->whereNotIn('booking_status', [ArrangementStatus::CANCELLED->value, ArrangementStatus::REJECTED->value])
            ->orderBy('location_id')
            ->orderBy('start_date')
            ->get()
            ->groupBy('location_id');

        foreach ($blocking as $locationId => $arrangements) {
            $arrangements->sliding(2)->each(function ($pair) use ($locationId) {
                [$earlier, $later] = [$pair->first(), $pair->last()];

                $this->assertTrue(
                    $earlier->end_date->lessThanOrEqualTo($later->start_date),
                    "Locatie {$locationId} is dubbel geboekt tussen {$later->start_date} en {$earlier->end_date}.",
                );
            });
        }
    }

    public function test_cancelled_bookings_do_not_block_their_location(): void
    {
        $this->seed(ArrangementsSeeder::class);

        $cancelled = Arrangement::where('booking_status', ArrangementStatus::CANCELLED->value)->first();

        $this->assertNotNull($cancelled, 'Er is geen geannuleerde boeking geseed.');

        $blockingOnSameLocation = Arrangement::query()
            ->where('location_id', $cancelled->location_id)
            ->whereNotIn('booking_status', [ArrangementStatus::CANCELLED->value, ArrangementStatus::REJECTED->value])
            ->where('start_date', '<', $cancelled->end_date)
            ->where('end_date', '>', $cancelled->start_date)
            ->count();

        $this->assertSame(1, $blockingOnSameLocation, 'De annulering hoort bovenop precies een lopende boeking te liggen.');
    }

    public function test_past_bookings_are_finished_and_future_bookings_are_not(): void
    {
        $this->seed(ArrangementsSeeder::class);

        $past = Arrangement::where('end_date', '<', now())
            ->whereNotIn('booking_status', [ArrangementStatus::CANCELLED->value, ArrangementStatus::REJECTED->value])
            ->get();

        $this->assertNotEmpty($past);

        foreach ($past as $arrangement) {
            $this->assertSame(ArrangementStatus::FINISHED->value, $arrangement->booking_status);
            $this->assertTrue($arrangement->payment_received);
        }

        foreach (Arrangement::where('start_date', '>', now())->get() as $arrangement) {
            $this->assertNotSame(ArrangementStatus::FINISHED->value, $arrangement->booking_status);
        }
    }
}
