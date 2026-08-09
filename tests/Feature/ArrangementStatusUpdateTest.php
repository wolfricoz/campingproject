<?php

namespace Tests\Feature;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ArrangementStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function updateStatus(Arrangement $arrangement, string $status): TestResponse
    {
        return $this->actingAs(User::factory()->create())->postJson(route('api.arrangements.status'), [
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
        $response = $this->actingAs(User::factory()->create())->postJson(route('api.arrangements.status'), [
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
}
