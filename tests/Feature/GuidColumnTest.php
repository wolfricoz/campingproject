<?php

namespace Tests\Feature;

use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * De guid-kolommen worden door de database zelf gevuld. Postgres en SQLite doen dat
 * met een andere expressie, deze test controleert dat het op beide werkt.
 */
class GuidColumnTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array<int, class-string>>
     */
    public static function modelProvider(): array
    {
        return [
            'user' => [User::class],
            'customer' => [Customer::class],
            'location' => [Location::class],
            'arrangement' => [Arrangement::class],
        ];
    }

    /**
     * @param  class-string  $model
     */
    #[DataProvider('modelProvider')]
    public function test_the_database_fills_the_guid_with_a_valid_uuid(string $model): void
    {
        $record = $model::factory()->create();

        $guid = DB::table($record->getTable())->where('id', $record->id)->value('guid');

        $this->assertTrue(Str::isUuid($guid), "Geen geldige uuid gegenereerd voor {$model}: ".var_export($guid, true));
    }

    public function test_every_record_gets_its_own_guid(): void
    {
        $customers = Customer::factory()->count(3)->create();

        $guids = DB::table('customers')->pluck('guid');

        $this->assertCount(3, $guids->unique(), 'Guids moeten uniek zijn per record.');
        $this->assertCount($customers->count(), $guids);
    }
}
