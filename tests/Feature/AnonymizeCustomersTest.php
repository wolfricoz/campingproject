<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The camping keeps customer details for seven years after the last stay and
 * removes them after that. The command that does so wipes data for good, so it
 * has to be exact about who it takes along and who it leaves alone.
 */
class AnonymizeCustomersTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The seven year boundary is only testable when the clock stands still:
     * otherwise a booking created "exactly seven years ago" is already a few
     * milliseconds too old by the time the command works out its cut-off.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->freezeTime();
    }

    private function customerFrom(string $registered): Customer
    {
        return Customer::factory()->create([
            'created_at' => now()->sub($registered),
            'name' => 'Sanne de Vries',
            'email' => 'sanne.devries@example.nl',
            'postal_code' => '4811 DL',
            'city' => 'Breda',
        ]);
    }

    private function bookingFor(Customer $customer, string $ago): void
    {
        Arrangement::factory()->create([
            'customer_id' => $customer->id,
            'created_at' => now()->sub($ago),
        ]);
    }

    // === Who is taken along.

    public function test_a_customer_whose_last_stay_is_older_than_seven_years_is_anonymized(): void
    {
        $customer = $this->customerFrom('9 years');
        $this->bookingFor($customer, '8 years');

        $this->artisan('customers:anonymize')->assertSuccessful();

        $this->assertSame('Klant Geanonimiseerd', $customer->fresh()->name);
    }

    public function test_every_identifiable_field_is_wiped(): void
    {
        $customer = $this->customerFrom('9 years');
        $this->bookingFor($customer, '8 years');

        $this->artisan('customers:anonymize');

        $customer = $customer->fresh();

        $this->assertSame('Klant Geanonimiseerd', $customer->name);
        $this->assertSame($customer->guid.'@syntec-camping.nl', $customer->email);
        $this->assertSame('**', $customer->street_name);
        $this->assertSame('**', $customer->street_number);
        $this->assertSame('**', $customer->postal_code);
        $this->assertSame('**', $customer->city);
        $this->assertSame('**', $customer->country);
    }

    public function test_the_account_behind_the_customer_is_deleted(): void
    {
        $user = User::factory()->create();
        $customer = $this->customerFrom('9 years');
        $customer->update(['user_id' => $user->id]);
        $this->bookingFor($customer, '8 years');

        $this->artisan('customers:anonymize');

        $this->assertNull(User::find($user->id));
        $this->assertNull($customer->fresh()->user_id);
    }

    // === Who is left alone.

    public function test_a_customer_who_still_books_is_left_alone(): void
    {
        $customer = $this->customerFrom('9 years');
        $this->bookingFor($customer, '8 years');
        $this->bookingFor($customer, '1 month');

        $this->artisan('customers:anonymize');

        $this->assertSame('Sanne de Vries', $customer->fresh()->name);
        $this->assertSame('4811 DL', $customer->fresh()->postal_code);
    }

    public function test_a_customer_without_any_booking_is_left_alone(): void
    {
        $customer = $this->customerFrom('9 years');

        $this->artisan('customers:anonymize');

        $this->assertSame('Sanne de Vries', $customer->fresh()->name);
    }

    public function test_a_customer_who_registered_recently_is_left_alone(): void
    {
        $customer = $this->customerFrom('2 years');
        $this->bookingFor($customer, '1 year');

        $this->artisan('customers:anonymize');

        $this->assertSame('Sanne de Vries', $customer->fresh()->name);
    }

    public function test_a_stay_of_exactly_seven_years_ago_keeps_the_customer(): void
    {
        $customer = $this->customerFrom('9 years');
        $this->bookingFor($customer, '7 years');

        $this->artisan('customers:anonymize');

        $this->assertSame('Sanne de Vries', $customer->fresh()->name);
    }

    // === What the command reports.

    public function test_it_counts_customers_and_not_bookings(): void
    {
        $customer = $this->customerFrom('9 years');
        $this->bookingFor($customer, '8 years');
        $this->bookingFor($customer, '9 years');
        $this->bookingFor($customer, '10 years');

        $this->artisan('customers:anonymize')
            ->expectsOutput('Anonymized 1 customers!');
    }

    public function test_it_reports_nothing_to_do_without_customers(): void
    {
        $this->artisan('customers:anonymize')
            ->expectsOutput('Anonymized 0 customers!')
            ->assertSuccessful();
    }

    // === The command has to run by itself.

    public function test_the_command_runs_every_day(): void
    {
        $commands = collect(app(Schedule::class)->events())
            ->map(fn ($event): string => (string) $event->command);

        $this->assertTrue(
            $commands->contains(fn (string $command): bool => str_contains($command, 'customers:anonymize')),
            'The anonymize command is not scheduled.'
        );
    }
}
