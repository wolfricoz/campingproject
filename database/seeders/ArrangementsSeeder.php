<?php

namespace Database\Seeders;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ArrangementsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locations = $this->locations();
        $customers = $this->customers();

        foreach ($locations as $location) {
            $this->fillAgenda($location, $customers);
        }

        $this->addCancelledBookings($customers);
    }

    private function fillAgenda(Location $location, Collection $customers): void
    {
        $cursor = now()->startOfMonth()->subMonthNoOverflow()->startOfDay();
        $lastDay = now()->endOfMonth()->addMonthNoOverflow()->startOfDay();

        while ($cursor->lessThan($lastDay)) {
            $cursor = $cursor->addDays(fake()->numberBetween(1, 7));

            if ($cursor->greaterThanOrEqualTo($lastDay)) {
                break;
            }

            $nights = fake()->numberBetween(2, 9);
            $start = $cursor->copy()->setTime(15, 0);
            $end = $cursor->copy()->addDays($nights)->setTime(11, 0);

            $this->createArrangement($location, $customers->random(), $start, $end, $this->statusFor($start, $end));

            $cursor = $end->copy()->startOfDay();
        }
    }

    private function addCancelledBookings(Collection $customers): void
    {
        $bookings = Arrangement::query()
            ->where('start_date', '>', now())
            ->with('location')
            ->inRandomOrder()
            ->limit(4)
            ->get();

        foreach ($bookings as $index => $booking) {
            $this->createArrangement(
                $booking->location,
                $customers->random(),
                $booking->start_date->copy(),
                $booking->end_date->copy(),
                $index % 2 === 0 ? ArrangementStatus::CANCELLED : ArrangementStatus::REJECTED,
            );
        }
    }

    private function createArrangement(Location $location, Customer $customer, Carbon $start, Carbon $end, ArrangementStatus $bookingStatus): void
    {
        $nights = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());
        $isPaid = in_array($bookingStatus, [ArrangementStatus::CONFIRMED, ArrangementStatus::CHECKEDIN, ArrangementStatus::FINISHED], true);

        Arrangement::factory()->create([
            'customer_id' => $customer->id,
            'location_id' => $location->id,
            'start_date' => $start,
            'end_date' => $end,
            'total_price' => round($nights * (float) $location->price_per_night, 2),
            'booking_status' => $bookingStatus->value,
            'source' => fake()->randomElement(['website', 'website', 'phone', 'walk-in', 'agency']),
            'confirmation_email_sent' => $bookingStatus !== ArrangementStatus::PENDING,
            'payment_received' => $isPaid,
            'status' => 1,
        ]);
    }

    private function statusFor(Carbon $start, Carbon $end): ArrangementStatus
    {
        if ($end->isPast()) {
            return ArrangementStatus::FINISHED;
        }

        if ($start->isPast()) {
            return ArrangementStatus::CHECKEDIN;
        }

        return fake()->boolean(70) ? ArrangementStatus::CONFIRMED : ArrangementStatus::PENDING;
    }

    /**
     * @return Collection<int, Location>
     */
    private function locations(): Collection
    {
        if (Location::where('status', 1)->doesntExist()) {
            $this->call(LocationsSeeder::class);
        }

        return Location::where('status', 1)->get();
    }

    /**
     * @return Collection<int, Customer>
     */
    private function customers(): Collection
    {
        if (Customer::where('status', 1)->doesntExist()) {
            $this->call(CustomerSeeder::class);
        }

        return Customer::where('status', 1)->get();
    }
}
