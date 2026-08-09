<?php

namespace Database\Seeders;

use App\Models\Arrangement;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('user'),

        ]);
        Arrangement::factory(10)->create();

        $this->call(NewsSeeder::class);
    }
}
