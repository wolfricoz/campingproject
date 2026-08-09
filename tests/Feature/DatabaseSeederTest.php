<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeding_the_application_fills_every_table(): void
    {
        $this->seed();

        $this->assertTrue(User::where('email', 'admin@syntec-camping.nl')->exists());
        $this->assertGreaterThanOrEqual(5, Location::count());
        $this->assertGreaterThan(0, Customer::count());
        $this->assertGreaterThan(0, Arrangement::count());
        $this->assertGreaterThan(0, News::count());

        $this->assertSame(0, Location::whereNull('guid')->count());
        $this->assertSame(0, Arrangement::whereNull('guid')->count());
        $this->assertSame(0, Customer::whereNull('guid')->count());
    }
}
