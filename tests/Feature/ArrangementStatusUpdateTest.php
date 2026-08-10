<?php

namespace Tests\Feature;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ArrangementStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        return User::factory()->create()->assignRole('administrator');
    }

    private function updateStatus(Arrangement $arrangement, string $status): TestResponse
    {
        return $this->actingAs($this->administrator())->postJson(route('api.arrangements.status'), [
            'id' => $arrangement->id,
            'status' => $status,
        ]);
    }

    public function test_it_writes_the_new_status_to_the_booking_status_column(): void
    {
        $arrangement = Arrangement::factory()->create([
            'booking_status' => 'pending',
            'status' => 1,
        ]);

        $response = $this->updateStatus($arrangement, 'confirmed');

        $response->assertOk()->assertJson(['status' => 'success!']);
        $this->assertDatabaseHas('arrangements', [
            'id' => $arrangement->id,
            'booking_status' => 'confirmed',
        ]);
    }

    public function test_it_leaves_the_active_flag_untouched(): void
    {
        $arrangement = Arrangement::factory()->create([
            'booking_status' => 'pending',
            'status' => 1,
        ]);

        $this->updateStatus($arrangement, 'cancelled')->assertOk();

        $this->assertSame(1, $arrangement->fresh()->status);
    }

    public function test_the_response_contains_the_updated_arrangement(): void
    {
        $arrangement = Arrangement::factory()->create(['booking_status' => 'pending']);

        $response = $this->updateStatus($arrangement, 'checked-in');

        $response->assertOk()->assertJsonPath('updated_data.booking_status', 'checked-in');
    }

    public function test_it_accepts_every_status_from_the_enum(): void
    {
        foreach (ArrangementStatus::cases() as $case) {
            $arrangement = Arrangement::factory()->create(['booking_status' => 'pending']);

            $this->updateStatus($arrangement, $case->value)->assertOk();

            $this->assertSame($case->value, $arrangement->fresh()->booking_status);
        }
    }

    public function test_it_refuses_a_status_that_is_not_in_the_enum(): void
    {
        $arrangement = Arrangement::factory()->create(['booking_status' => 'pending']);

        $response = $this->updateStatus($arrangement, 'zomaar-iets');

        $response->assertStatus(422)->assertJsonValidationErrors('status');
        $this->assertSame('pending', $arrangement->fresh()->booking_status);
    }

    public function test_it_refuses_an_arrangement_that_does_not_exist(): void
    {
        $response = $this->actingAs($this->administrator())->postJson(route('api.arrangements.status'), [
            'id' => 9999,
            'status' => 'confirmed',
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('id');
    }

    public function test_it_changes_nothing_but_the_status(): void
    {
        $arrangement = Arrangement::factory()->create(['booking_status' => 'pending']);
        $before = $arrangement->only(['customer_id', 'location_id', 'total_price', 'source']);

        $this->updateStatus($arrangement, 'finished')->assertOk();

        $this->assertSame($before, $arrangement->fresh()->only(array_keys($before)));
    }

    public function test_a_guest_may_not_change_a_status(): void
    {
        $arrangement = Arrangement::factory()->create(['booking_status' => 'pending']);

        $response = $this->postJson(route('api.arrangements.status'), [
            'id' => $arrangement->id,
            'status' => 'confirmed',
        ]);

        $response->assertStatus(401);
        $this->assertSame('pending', $arrangement->fresh()->booking_status);
    }

    public function test_a_customer_may_not_change_a_status(): void
    {
        $arrangement = Arrangement::factory()->create(['booking_status' => 'pending']);

        $response = $this->actingAs(User::factory()->create()->assignRole('customer'))
            ->postJson(route('api.arrangements.status'), [
                'id' => $arrangement->id,
                'status' => 'confirmed',
            ]);

        $response->assertForbidden();
        $this->assertSame('pending', $arrangement->fresh()->booking_status);
    }

    /**
     * A receptionist may run the day to day statuses, but approving and
     * rejecting a reservation is up to an administrator.
     */
    public function test_a_receptionist_may_check_in_but_may_not_approve_or_reject(): void
    {
        $receptionist = User::factory()->create()->assignRole('receptionist');

        $allowed = Arrangement::factory()->create(['booking_status' => 'pending']);

        $this->actingAs($receptionist)->postJson(route('api.arrangements.status'), [
            'id' => $allowed->id,
            'status' => ArrangementStatus::CHECKEDIN->value,
        ])->assertOk();

        foreach ([ArrangementStatus::CONFIRMED, ArrangementStatus::REJECTED] as $status) {
            $refused = Arrangement::factory()->create(['booking_status' => 'pending']);

            $this->actingAs($receptionist)->postJson(route('api.arrangements.status'), [
                'id' => $refused->id,
                'status' => $status->value,
            ])->assertForbidden();

            $this->assertSame('pending', $refused->fresh()->booking_status);
        }
    }

    public function test_every_status_maps_onto_an_existing_permission(): void
    {
        $known = Permission::query()->pluck('name')->all();

        foreach (ArrangementStatus::cases() as $case) {
            $this->assertContains($case->permission(), $known, "Status {$case->value} points at an unknown permission.");
        }
    }
}
