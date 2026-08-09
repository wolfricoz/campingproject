<?php

namespace Tests\Feature;

use App\Models\Customer;
use Database\Seeders\CustomerSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class CustomerSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_the_fixed_guests_and_some_extra_ones(): void
    {
        $this->seed(CustomerSeeder::class);

        $this->assertSame(18, Customer::count());
        $this->assertDatabaseHas('customers', [
            'name' => 'Sanne de Vries',
            'email' => 'sanne.devries@example.nl',
            'city' => 'Hilvarenbeek',
        ]);
    }

    public function test_every_seeded_customer_is_complete(): void
    {
        $this->seed(CustomerSeeder::class);

        foreach (Customer::all() as $customer) {
            $this->assertTrue(Str::isUuid($customer->guid), 'Klant kreeg geen geldige uuid als guid.');
            $this->assertNotEmpty($customer->name);
            $this->assertNotEmpty($customer->phone_number);
            $this->assertNotEmpty($customer->street_name);
            $this->assertNotEmpty($customer->city);
            $this->assertNotEmpty($customer->country);
            $this->assertSame(1, $customer->status);
        }
    }

    public function test_the_email_addresses_stay_unique(): void
    {
        $this->seed(CustomerSeeder::class);

        $emails = Customer::pluck('email');

        $this->assertCount($emails->unique()->count(), $emails);
    }

    /**
     * De gasten zijn Nederlandstalig, dus de factory hoort geen Amerikaanse postcodes
     * en woonplaatsen meer te genereren.
     */
    public function test_the_factory_generates_a_dutch_postal_code(): void
    {
        $customer = Customer::factory()->create();

        $this->assertMatchesRegularExpression('/^\d{4} [A-Z]{2}$/', $customer->postal_code);
        $this->assertContains($customer->country, ['Nederland', 'België', 'Duitsland']);
    }
}
