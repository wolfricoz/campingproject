<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ArrangementOverviewTest extends TestCase
{
    use RefreshDatabase;

    private function receptionist(): User
    {
        return User::factory()->create()->assignRole('receptionist');
    }

    public function test_it_returns_the_reservations_per_page(): void
    {
        Arrangement::factory()->count(30)->create(['status' => 1]);

        $response = $this->actingAs($this->receptionist())->get(route('arrangement.index'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/Arrangements/Index')
                ->count('arrangements.data', 25)
                ->where('arrangements.total', 30)
        );
    }

    public function test_the_second_page_holds_the_rest(): void
    {
        Arrangement::factory()->count(30)->create(['status' => 1]);

        $response = $this->actingAs($this->receptionist())->get(route('arrangement.index', ['page' => 2]));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->count('arrangements.data', 5)
        );
    }

    public function test_it_searches_on_the_customer_and_on_the_location(): void
    {
        $wanted = Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Rico de Jong']),
            'start_date' => now()->addDays(10),
            'status' => 1,
        ]);
        $onLocation = Arrangement::factory()->create([
            'location_id' => Location::factory()->create(['name' => 'Chalet Rico']),
            'start_date' => now()->addDays(2),
            'status' => 1,
        ]);
        Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Piet Jansen']),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['search' => 'Rico']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->count('arrangements.data', 2)
                ->where('arrangements.data.0.id', $wanted->id)
                ->where('arrangements.data.1.id', $onLocation->id)
        );
    }

    public function test_it_searches_on_the_customer_email(): void
    {
        Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['email' => 'kampeerder@voorbeeld.nl']),
            'status' => 1,
        ]);
        Arrangement::factory()->create(['status' => 1]);

        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['search' => 'kampeerder@voorbeeld.nl']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->count('arrangements.data', 1)
        );
    }

    public function test_it_sorts_on_the_price(): void
    {
        Arrangement::factory()->create(['total_price' => 100, 'status' => 1]);
        Arrangement::factory()->create(['total_price' => 900, 'status' => 1]);

        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['sort' => 'total_price', 'direction' => 'asc']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->where('arrangements.data.0.total_price', 100)
        );
    }

    public function test_it_sorts_on_the_name_of_the_customer(): void
    {
        Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Zoë Vermeer']),
            'status' => 1,
        ]);
        Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Anna Bakker']),
            'status' => 1,
        ]);

        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['sort' => 'customer', 'direction' => 'asc']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->where('arrangements.data.0.customer.name', 'Anna Bakker')
        );
    }

    /**
     * Sorting on a column that is not allowed may not blow up the query.
     */
    public function test_an_unknown_sort_falls_back_to_the_arrival_date(): void
    {
        $later = Arrangement::factory()->create(['start_date' => now()->addDays(10), 'status' => 1]);
        Arrangement::factory()->create(['start_date' => now()->addDays(2), 'status' => 1]);

        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['sort' => 'total_price); drop table arrangements;--']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->where('arrangements.data.0.id', $later->id)
        );
    }

    /**
     * The search may not pull in reservations from another status or from the archive.
     */
    public function test_the_search_stays_inside_the_status_and_the_active_reservations(): void
    {
        $wanted = Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Rico de Jong']),
            'booking_status' => 'pending',
            'status' => 1,
        ]);
        Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Rico de Vries']),
            'booking_status' => 'confirmed',
            'status' => 1,
        ]);
        Arrangement::factory()->create([
            'customer_id' => Customer::factory()->create(['name' => 'Rico Archief']),
            'booking_status' => 'pending',
            'status' => 0,
        ]);

        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['status' => 'pending', 'search' => 'Rico']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('arrangements.total', 1)
                ->where('arrangements.data.0.id', $wanted->id)
        );
    }

    public function test_it_sends_the_used_filters_back_to_the_page(): void
    {
        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['search' => 'Rico', 'sort' => 'end_date', 'direction' => 'asc']));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('filters.search', 'Rico')
                ->where('filters.sort', 'end_date')
                ->where('filters.direction', 'asc')
        );
    }

    public function test_it_refuses_a_direction_it_does_not_know(): void
    {
        $response = $this->actingAs($this->receptionist())
            ->get(route('arrangement.index', ['direction' => 'sideways']));

        $response->assertSessionHasErrors('direction');
    }

    public function test_the_status_in_the_url_still_filters_the_list(): void
    {
        Arrangement::factory()->count(2)->create(['booking_status' => 'pending', 'status' => 1]);
        Arrangement::factory()->create(['booking_status' => 'confirmed', 'status' => 1]);

        $response = $this->actingAs($this->receptionist())->get(route('arrangement.index', 'pending'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->where('arrangements.total', 2)
        );
    }
}
