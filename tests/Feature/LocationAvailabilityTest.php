<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LocationAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    private function inDays(int $days): string
    {
        return now()->addDays($days)->format('Y-m-d\TH:i');
    }

    private function storedInDays(int $days): string
    {
        return now()->addDays($days)->format('Y-m-d H:i:s');
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(Location $location, string $start, string $end): array
    {
        return [
            'location_id' => $location->id,
            'start_date' => $start,
            'end_date' => $end,
            'customer' => [
                'name' => 'Jan Jansen',
                'email' => 'jan@voorbeeld.nl',
                'phone_number' => '06 12345678',
                'street_name' => 'Dorpsstraat',
                'street_number' => '1',
                'postal_code' => '1234 AB',
                'city' => 'Ergens',
                'country' => 'Nederland',
                'create_account' => false,
            ],
        ];
    }

    public function test_the_availability_endpoint_is_reachable_for_a_guest_and_reports_a_free_location(): void
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('api.locations.available'), [
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(14),
        ]);

        $response->assertOk()->assertJson(['available' => true]);
    }

    public function test_the_availability_endpoint_reports_an_occupied_location_with_a_message(): void
    {
        $location = Location::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(12),
            'end_date' => $this->storedInDays(16),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->postJson(route('api.locations.available'), [
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(14),
        ]);

        $response->assertOk()->assertJson(['available' => false]);
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_the_availability_endpoint_refuses_a_start_date_in_the_past(): void
    {
        $location = Location::factory()->create();

        $response = $this->postJson(route('api.locations.available'), [
            'location_id' => $location->id,
            'start_date' => $this->inDays(-3),
            'end_date' => $this->inDays(3),
        ]);

        $response->assertOk();
        $this->assertNotTrue($response->json('available'));
        $this->assertNotEmpty($response->json('message'));
    }

    public function test_every_message_the_endpoint_returns_has_an_english_translation(): void
    {
        $translations = json_decode(file_get_contents(lang_path('en.json')), true);

        $location = Location::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(12),
            'end_date' => $this->storedInDays(16),
            'booking_status' => 'confirmed',
        ]);

        $messages = [
            $this->postJson(route('api.locations.available'), [
                'location_id' => $location->id,
                'start_date' => $this->inDays(10),
                'end_date' => $this->inDays(14),
            ])->json('message'),
            $this->postJson(route('api.locations.available'), [
                'location_id' => $location->id,
                'start_date' => $this->inDays(-3),
                'end_date' => $this->inDays(3),
            ])->json('message'),
        ];

        foreach ($messages as $message) {
            $this->assertArrayHasKey($message, $translations, "Geen Engelse vertaling voor: {$message}");
        }
    }

    public function test_a_cancelled_arrangement_does_not_occupy_the_location(): void
    {
        $location = Location::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(12),
            'end_date' => $this->storedInDays(16),
            'booking_status' => 'cancelled',
        ]);

        $response = $this->postJson(route('api.locations.available'), [
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(14),
        ]);

        $response->assertOk()->assertJson(['available' => true]);
    }

    public function test_an_arrangement_that_is_being_edited_does_not_block_itself(): void
    {
        $location = Location::factory()->create();
        $arrangement = Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(10),
            'end_date' => $this->storedInDays(14),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->postJson(route('api.locations.available'), [
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(15),
            'arrangement_id' => $arrangement->id,
        ]);

        $response->assertOk()->assertJson(['available' => true]);
    }

    public function test_a_stay_that_starts_when_another_one_ends_is_still_available(): void
    {
        $location = Location::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(6),
            'end_date' => $this->storedInDays(10),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->postJson(route('api.locations.available'), [
            'location_id' => $location->id,
            'start_date' => now()->addDays(10)->format('Y-m-d\TH:i:s'),
            'end_date' => $this->inDays(14),
        ]);

        $response->assertOk()->assertJson(['available' => true]);
    }

    public function test_the_arrangement_store_refuses_an_occupied_location(): void
    {
        $location = Location::factory()->create();
        $customer = Customer::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(12),
            'end_date' => $this->storedInDays(16),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.arrangements.store'), [
            'id' => 0,
            'customer_id' => $customer->id,
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(14),
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('location_id');
        $this->assertDatabaseCount('arrangements', 1);
    }

    public function test_the_arrangement_store_accepts_a_free_location(): void
    {
        $location = Location::factory()->create();
        $customer = Customer::factory()->create();

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.arrangements.store'), [
            'id' => 0,
            'customer_id' => $customer->id,
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(14),
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('arrangements', 1);
    }

    public function test_the_arrangement_store_can_still_update_an_existing_arrangement(): void
    {
        $location = Location::factory()->create();
        $arrangement = Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(10),
            'end_date' => $this->storedInDays(14),
            'booking_status' => 'confirmed',
        ]);

        $response = $this->actingAs(User::factory()->create())->postJson(route('api.arrangements.store'), [
            'id' => $arrangement->id,
            'customer_id' => $arrangement->customer_id,
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(15),
        ]);

        $response->assertOk();
        $this->assertDatabaseCount('arrangements', 1);
    }

    public function test_the_booking_page_refuses_an_occupied_location_and_creates_no_customer(): void
    {
        Mail::fake();

        $location = Location::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => $this->storedInDays(12),
            'end_date' => $this->storedInDays(16),
            'booking_status' => 'confirmed',
        ]);
        $customerCount = Customer::count();

        $response = $this->post(
            route('booking.store'),
            $this->bookingPayload($location, $this->inDays(10), $this->inDays(14))
        );

        $response->assertSessionHasErrors('location_id');
        $this->assertDatabaseCount('arrangements', 1);
        $this->assertSame($customerCount, Customer::count());
    }

    public function test_the_booking_page_accepts_a_free_location(): void
    {
        Mail::fake();

        $location = Location::factory()->create();

        $response = $this->post(
            route('booking.store'),
            $this->bookingPayload($location, $this->inDays(10), $this->inDays(14))
        );

        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('arrangements', 1);
    }
}
