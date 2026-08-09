<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->customers() as $customer) {
            Customer::factory()->create($customer);
        }

        Customer::factory(12)->create();
    }

    /**
     * @return array<int, array{name: string, email: string, phone_number: string, street_name: string, street_number: string, postal_code: string, city: string, country: string}>
     */
    private function customers(): array
    {
        return [
            [
                'name' => 'Sanne de Vries',
                'email' => 'sanne.devries@example.nl',
                'phone_number' => '06-24815903',
                'street_name' => 'Dorpsstraat',
                'street_number' => '18',
                'postal_code' => '5081 AA',
                'city' => 'Hilvarenbeek',
                'country' => 'Nederland',
            ],
            [
                'name' => 'Mark Jansen',
                'email' => 'mark.jansen@example.nl',
                'phone_number' => '06-13947720',
                'street_name' => 'Beukenlaan',
                'street_number' => '142',
                'postal_code' => '5616 VC',
                'city' => 'Eindhoven',
                'country' => 'Nederland',
            ],
            [
                'name' => 'Fatima el Amrani',
                'email' => 'f.elamrani@example.nl',
                'phone_number' => '06-38820147',
                'street_name' => 'Kanaalweg',
                'street_number' => '7',
                'postal_code' => '3526 KL',
                'city' => 'Utrecht',
                'country' => 'Nederland',
            ],
            [
                'name' => 'Peter Bakker',
                'email' => 'peter.bakker@example.nl',
                'phone_number' => '06-45210398',
                'street_name' => 'Molenweg',
                'street_number' => '63',
                'postal_code' => '7031 HK',
                'city' => 'Wehl',
                'country' => 'Nederland',
            ],
            [
                'name' => 'Lieve Vermeulen',
                'email' => 'lieve.vermeulen@example.be',
                'phone_number' => '0032-478112094',
                'street_name' => 'Kerkstraat',
                'street_number' => '24',
                'postal_code' => '2300 AB',
                'city' => 'Turnhout',
                'country' => 'België',
            ],
            [
                'name' => 'Thomas Müller',
                'email' => 'thomas.mueller@example.de',
                'phone_number' => '0049-1755390218',
                'street_name' => 'Bahnhofstraße',
                'street_number' => '9',
                'postal_code' => '4650 GH',
                'city' => 'Kleve',
                'country' => 'Duitsland',
            ],
        ];
    }
}
