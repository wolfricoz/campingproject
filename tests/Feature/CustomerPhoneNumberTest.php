<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * The desk looks a customer up on the combination of e-mail address and phone
 * number. That only works when every screen writes the phone number down in the
 * same notation, which is what these tests guard.
 */
class CustomerPhoneNumberTest extends TestCase
{
    use RefreshDatabase;

    private function receptionist(): User
    {
        return User::factory()->create()->assignRole('receptionist');
    }

    // === The number is stored in one notation, whatever was typed in.

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function notations(): array
    {
        return [
            'plain' => ['0624815903', '0624815903'],
            'with a dash' => ['06-24815903', '0624815903'],
            'with spaces' => ['06 24 81 59 03', '0624815903'],
            'with surrounding spaces' => ['  0624815903 ', '0624815903'],
            'belgian, written as the seeder does' => ['0032-478112094', '0032478112094'],
            'german, written as the seeder does' => ['0049-1755390218', '00491755390218'],
            'with a plus instead of the double zero' => ['+32478112094', '0032478112094'],
        ];
    }

    #[DataProvider('notations')]
    public function test_the_stored_number_keeps_only_its_digits(string $typed, string $stored): void
    {
        $customer = Customer::factory()->create(['phone_number' => $typed]);

        $this->assertSame($stored, $customer->fresh()->phone_number);
    }

    public function test_the_factory_stores_a_number_without_punctuation(): void
    {
        $customer = Customer::factory()->create();

        $this->assertMatchesRegularExpression('/^\d{10,15}$/', $customer->fresh()->phone_number);
    }

    // === What the screens show.

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function formattedNotations(): array
    {
        return [
            'a dutch mobile number gets its dash back' => ['0624815903', '06-24815903'],
            'an international number is shown with a plus' => ['0032478112094', '+32478112094'],
            'a landline is shown as it is stored' => ['0765224417', '0765224417'],
        ];
    }

    #[DataProvider('formattedNotations')]
    public function test_the_screens_get_a_readable_version_of_the_number(string $stored, string $shown): void
    {
        $customer = Customer::factory()->create(['phone_number' => $stored]);

        $this->assertSame($shown, $customer->fresh()->phone_number_formatted);
    }

    public function test_the_readable_version_travels_along_to_the_screen(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'sanne.devries@example.nl',
            'phone_number' => '06-24815903',
        ]);

        $this->actingAs($this->receptionist())
            ->getJson(route('api.customers.find', [
                'id' => $customer->id,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
            ]))
            ->assertOk()
            ->assertJsonFragment([
                'phone_number' => '0624815903',
                'phone_number_formatted' => '06-24815903',
            ]);
    }

    // === Looking a customer up at the desk.

    public function test_a_receptionist_finds_a_customer_that_the_factory_created(): void
    {
        $customer = Customer::factory()->create(['email' => 'sanne.devries@example.nl']);

        $this->actingAs($this->receptionist())
            ->getJson(route('api.customers.find', [
                'id' => $customer->id,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
            ]))
            ->assertOk()
            ->assertJsonFragment(['email' => 'sanne.devries@example.nl']);
    }

    #[DataProvider('notations')]
    public function test_the_desk_finds_the_customer_however_the_number_is_typed(string $typed, string $stored): void
    {
        $customer = Customer::factory()->create([
            'email' => 'sanne.devries@example.nl',
            'phone_number' => $stored,
        ]);

        $this->actingAs($this->receptionist())
            ->getJson(route('api.customers.find', [
                'id' => $customer->id,
                'email' => $customer->email,
                'phone_number' => $typed,
            ]))
            ->assertOk()
            ->assertJsonFragment(['email' => 'sanne.devries@example.nl']);
    }

    public function test_an_unknown_combination_returns_nothing(): void
    {
        Customer::factory()->create([
            'email' => 'sanne.devries@example.nl',
            'phone_number' => '0624815903',
        ]);

        $response = $this->actingAs($this->receptionist())
            ->getJson(route('api.customers.find', [
                'id' => 1,
                'email' => 'sanne.devries@example.nl',
                'phone_number' => '0611111111',
            ]))
            ->assertOk();

        $this->assertEmpty($response->json());
    }

    // === Validation of what the desk types in.

    public function test_a_number_with_too_few_digits_is_refused(): void
    {
        $this->actingAs($this->receptionist())
            ->getJson(route('api.customers.find', [
                'id' => 1,
                'email' => 'sanne.devries@example.nl',
                'phone_number' => '06-1234',
            ]))
            ->assertJsonValidationErrorFor('phone_number');
    }

    public function test_a_number_without_any_digits_is_refused(): void
    {
        $this->actingAs($this->receptionist())
            ->getJson(route('api.customers.find', [
                'id' => 1,
                'email' => 'sanne.devries@example.nl',
                'phone_number' => 'geen idee',
            ]))
            ->assertJsonValidationErrorFor('phone_number');
    }

    // === The same person may not end up in the database twice.

    public function test_a_returning_guest_who_types_the_number_differently_stays_one_customer(): void
    {
        Customer::factory()->create([
            'email' => 'sanne.devries@example.nl',
            'phone_number' => '06-24815903',
        ]);

        Customer::createNewCustomer([
            'name' => 'Sanne de Vries',
            'email' => 'sanne.devries@example.nl',
            'phone_number' => '06 24 81 59 03',
            'city' => 'Breda',
            'create_account' => false,
        ]);

        $this->assertSame(1, Customer::where('email', 'sanne.devries@example.nl')->count());
        $this->assertSame('Breda', Customer::where('email', 'sanne.devries@example.nl')->first()->city);
    }
}
