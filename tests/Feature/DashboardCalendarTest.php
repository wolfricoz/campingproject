<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * De kalender op het dashboard toont standaard deze maand, en met ?month=YYYY-MM een andere.
 */
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

    /**
     * Een verblijf dat vóór de maand begint en erna pas eindigt hoort de hele maand zichtbaar
     * te zijn; met een whereBetween op alleen de begin- of einddatum zou hij wegvallen.
     */
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

    /**
     * Een gebruiker die het planningsdashboard mag openen.
     */
    private function planner(): User
    {
        Permission::findOrCreate('access dashboard');

        $user = User::factory()->create();
        $user->givePermissionTo('access dashboard');

        return $user;
    }
}
