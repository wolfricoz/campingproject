<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DashboardCalendarTest extends TestCase
{
    use RefreshDatabase;

    public function test_without_a_month_parameter_the_calendar_shows_the_current_month(): void
    {
        $response = $this->actingAs($this->planner())->get(route('dashboard'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/Dashboard')
                ->where('month', now()->format('Y-m'))
        );
    }

    /**
     * The list under the calendar prints the phone number, and it prints the
     * readable version of it. That version is built on the server, so it has to
     * travel along with the arrangement.
     */
    public function test_the_calendar_carries_the_readable_phone_number(): void
    {
        $arrangement = Arrangement::factory()->create([
            'start_date' => now()->startOfMonth()->addDays(3),
            'end_date' => now()->startOfMonth()->addDays(6),
            'booking_status' => 'confirmed',
        ]);
        $arrangement->customer->update(['phone_number' => '06-24815903']);

        $this->actingAs($this->planner())->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(
                fn (AssertableInertia $page) => $page
                    ->where('arrangements.0.customer.phone_number', '0624815903')
                    ->where('arrangements.0.customer.phone_number_formatted', '06-24815903')
            );
    }

    public function test_the_month_parameter_selects_another_month(): void
    {
        $next = now()->addMonthNoOverflow();

        $inNextMonth = Arrangement::factory()->create([
            'start_date' => $next->copy()->startOfMonth()->addDays(3),
            'end_date' => $next->copy()->startOfMonth()->addDays(6),
            'booking_status' => 'confirmed',
        ]);
        Arrangement::factory()->create([
            'start_date' => now()->startOfMonth()->addDays(3),
            'end_date' => now()->startOfMonth()->addDays(6),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->planner())
            ->get(route('dashboard', ['month' => $next->format('Y-m')]));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->where('month', $next->format('Y-m'))
                ->has('arrangements', 1)
                ->where('arrangements.0.id', $inNextMonth->id)
        );
    }

    public function test_an_arrangement_spanning_the_whole_month_is_included(): void
    {
        Arrangement::factory()->create([
            'start_date' => now()->startOfMonth()->subDays(5),
            'end_date' => now()->endOfMonth()->addDays(5),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs($this->planner())->get(route('dashboard'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->has('arrangements', 1)
        );
    }

    public function test_cancelled_and_rejected_arrangements_stay_out_of_the_calendar(): void
    {
        foreach (['cancelled', 'rejected'] as $status) {
            Arrangement::factory()->create([
                'start_date' => now()->startOfMonth()->addDays(3),
                'end_date' => now()->startOfMonth()->addDays(6),
                'booking_status' => $status,
            ]);
        }

        $response = $this->actingAs($this->planner())->get(route('dashboard'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page->has('arrangements', 0)
        );
    }

    public function test_a_malformed_month_parameter_is_rejected(): void
    {
        $response = $this->actingAs($this->planner())
            ->get(route('dashboard', ['month' => 'volgende-maand']));

        $response->assertSessionHasErrors('month');
    }

    private function planner(): User
    {
        Permission::findOrCreate('access dashboard');

        $user = User::factory()->create();
        $user->givePermissionTo('access dashboard');

        return $user;
    }
}
