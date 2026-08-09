<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ModelGuidTest extends TestCase
{
    use RefreshDatabase;

    /**
     * The guid used to be filled by the Postgres `gen_random_uuid()` default. Now that we run on
     * SQLite, the models generate it themselves through HasUuids.
     */
    public function test_models_generate_a_guid_on_create(): void
    {
        $user = User::create([
            'name' => 'Rico',
            'email' => 'rico@example.com',
            'password' => 'secret-password',
        ]);

        $customer = Customer::create([
            'name' => 'Rico',
            'email' => 'rico@example.com',
            'phone_number' => '0612345678',
            'street_name' => 'Dorpsstraat',
            'street_number' => '1',
            'postal_code' => '1234 AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'status' => 1,
        ]);

        $location = Location::factory()->create(['guid' => null]);

        $arrangement = Arrangement::create([
            'customer_id' => $customer->id,
            'location_id' => $location->id,
            'start_date' => now(),
            'end_date' => now()->addDays(3),
        ]);

        foreach ([$user, $customer, $location, $arrangement] as $model) {
            $this->assertTrue(
                Str::isUuid($model->guid),
                $model::class.' kreeg geen geldige uuid als guid.'
            );
        }
    }

    public function test_the_primary_key_stays_an_auto_incrementing_integer(): void
    {
        $user = User::factory()->create();

        $this->assertTrue($user->getIncrementing());
        $this->assertSame('int', $user->getKeyType());
        $this->assertIsInt($user->id);
    }
}
