<?php

namespace Database\Seeders;

use App\Enums\ArrangementStatus;
use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();
        $this->call(RolesAndPermissionsSeeder::class);

        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@syntec-camping.nl',
            'password' => bcrypt('admin'),
        ])->assignRole('administrator');

        $demoCustomer = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('user'),
        ]);

        $demoCustomer->assignRole('customer');

        $this->call([
            LocationsSeeder::class,
            CustomerSeeder::class,
            ArrangementsSeeder::class,
            NewsSeeder::class,
        ]);

        $this->giveTheDemoAccountItsOwnReservations($demoCustomer);
    }

    /**
     * The demo account needs reservations of its own, otherwise logging in with
     * test@example.com shows an empty dashboard. Existing bookings are handed
     * over instead of added, so no location ends up double booked.
     */
    private function giveTheDemoAccountItsOwnReservations(User $user): void
    {
        $customer = Customer::factory()->create([
            'name' => $user->name,
            'email' => $user->email,
            'user_id' => $user->id,
        ]);

        $statuses = [
            ArrangementStatus::FINISHED,
            ArrangementStatus::CHECKEDIN,
            ArrangementStatus::CONFIRMED,
            ArrangementStatus::PENDING,
        ];

        foreach ($statuses as $status) {
            Arrangement::query()
                ->where('booking_status', $status->value)
                ->inRandomOrder()
                ->first()
                ?->update(['customer_id' => $customer->id]);
        }
    }
}
