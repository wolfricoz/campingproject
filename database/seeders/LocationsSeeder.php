<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->locations() as $location) {
            Location::factory()->create([...$location, 'status' => 1]);
        }
    }

    /**
     * @return array<int, array{name: string, type: string, description: string, photo: ?string, capacity: int, bedrooms: int, size: float, price_per_night: float, has_electricity: bool, has_water: bool, has_shade: bool, is_advertised: bool}>
     */
    private function locations(): array
    {
        return [
            [
                'name' => 'Chalet De Eik',
                'type' => 'cabin',
                'description' => 'Chalet met twee slaapkamers onder de oude eiken aan de rand van het bos. '
                    .'De hele middag schaduw en op twee minuten lopen van het sanitairgebouw.',
                'photo' => '/images/header.jpg',
                'capacity' => 4,
                'bedrooms' => 2,
                'size' => 42.00,
                'price_per_night' => 89.50,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => true,
                'is_advertised' => true,
            ],
            [
                'name' => 'Chalet Zonneweide',
                'type' => 'cabin',
                'description' => 'Chalet met drie slaapkamers midden op het terrein, de hele dag in de zon. '
                    .'Ideaal voor gezinnen: de speelweide ligt er direct naast.',
                'photo' => '/images/header.jpg',
                'capacity' => 6,
                'bedrooms' => 3,
                'size' => 58.00,
                'price_per_night' => 110.00,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => false,
                'is_advertised' => false,
            ],
            [
                'name' => 'Chalet De Beuk',
                'type' => 'cabin',
                'description' => 'Chalet met eigen oprit en een 16A stroompunt, met de auto goed bereikbaar. '
                    .'Een hoge beukenhaag rondom het terras zorgt voor privacy.',
                'photo' => '/images/header.jpg',
                'capacity' => 5,
                'bedrooms' => 2,
                'size' => 52.00,
                'price_per_night' => 105.00,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => true,
                'is_advertised' => true,
            ],
            [
                'name' => 'Chalet Heideveld',
                'type' => 'cabin',
                'description' => 'Rustig gelegen chalet aan de heidekant van de camping, '
                    .'met uitzicht over het ven en een eigen picknicktafel op het terras.',
                'photo' => null,
                'capacity' => 4,
                'bedrooms' => 2,
                'size' => 46.00,
                'price_per_night' => 98.00,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => false,
                'is_advertised' => false,
            ],
            [
                'name' => 'Chalet Boslust',
                'type' => 'cabin',
                'description' => 'Volledig ingericht chalet met twee slaapkamers, een badkamer met douche '
                    .'en een overdekt terras aan de bosrand.',
                'photo' => '/images/header.jpg',
                'capacity' => 4,
                'bedrooms' => 2,
                'size' => 45.00,
                'price_per_night' => 95.00,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => true,
                'is_advertised' => true,
            ],
            [
                'name' => 'Chalet Vennezicht',
                'type' => 'cabin',
                'description' => 'Ruim familiechalet met drie slaapkamers, vaatwasser en een groot terras '
                    .'met zicht op het ven. Huisdieren zijn hier toegestaan.',
                'photo' => '/images/header.jpg',
                'capacity' => 6,
                'bedrooms' => 3,
                'size' => 62.00,
                'price_per_night' => 125.00,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => false,
                'is_advertised' => true,
            ],
            [
                'name' => 'Trekkershut Duinzicht',
                'type' => 'cabin',
                'description' => 'Compacte trekkershut met eigen keukenblok en een terras aan de duinkant. '
                    .'Aankomst en vertrek kan hier de hele dag door.',
                'photo' => null,
                'capacity' => 3,
                'bedrooms' => 1,
                'size' => 24.00,
                'price_per_night' => 60.00,
                'has_electricity' => true,
                'has_water' => true,
                'has_shade' => false,
                'is_advertised' => false,
            ],
            [
                'name' => 'Trekkershut De Specht',
                'type' => 'cabin',
                'description' => 'Eenvoudige trekkershut met stapelbed en kitchenette, bedoeld voor een korte '
                    .'stop van wandelaars en fietsers. Sanitair is gedeeld.',
                'photo' => null,
                'capacity' => 4,
                'bedrooms' => 1,
                'size' => 20.00,
                'price_per_night' => 55.00,
                'has_electricity' => true,
                'has_water' => false,
                'has_shade' => true,
                'is_advertised' => false,
            ],
        ];
    }
}
