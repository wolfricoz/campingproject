<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoutePermissionsTest extends TestCase
{
    use RefreshDatabase;

    private function userWithRole(string $role): User
    {
        return User::factory()->create()->assignRole($role);
    }

    // === Customer data is personal data: only staff with `view customers` sees it.

    public function test_a_customer_may_not_read_the_customer_list(): void
    {
        Customer::factory()->create();

        $this->actingAs($this->userWithRole('customer'))
            ->getJson(route('api.customers.index'))
            ->assertForbidden();
    }

    public function test_a_user_without_a_role_may_not_read_the_customer_list(): void
    {
        Customer::factory()->create();

        $this->actingAs(User::factory()->create())
            ->getJson(route('api.customers.index'))
            ->assertForbidden();
    }

    public function test_a_guest_may_not_read_the_customer_list(): void
    {
        $this->getJson(route('api.customers.index'))->assertUnauthorized();
    }

    public function test_a_receptionist_may_read_the_customer_list(): void
    {
        Customer::factory()->create(['name' => 'Jan Jansen']);

        $this->actingAs($this->userWithRole('receptionist'))
            ->getJson(route('api.customers.index'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Jan Jansen']);
    }

    public function test_the_customer_list_leaves_out_the_columns_the_screen_does_not_use(): void
    {
        Customer::factory()->create();

        $response = $this->actingAs($this->userWithRole('receptionist'))
            ->getJson(route('api.customers.index'));

        $response->assertOk();
        $this->assertArrayNotHasKey('guid', $response->json('0'));
        $this->assertArrayNotHasKey('created_at', $response->json('0'));
    }

    // === Managing locations is administrator work.

    public function test_a_receptionist_may_not_manage_locations(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->get(route('locations.index'))
            ->assertForbidden();
    }

    public function test_an_administrator_may_manage_locations(): void
    {
        $this->actingAs($this->userWithRole('administrator'))
            ->get(route('locations.index'))
            ->assertOk();
    }

    public function test_a_receptionist_may_not_store_a_location(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->postJson(route('api.locations.store'), Location::factory()->raw(['id' => null]))
            ->assertForbidden();
    }

    // === News is receptionist work.

    public function test_a_customer_may_not_reach_the_news_admin(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('news.index'))
            ->assertForbidden();
    }

    public function test_a_receptionist_may_reach_the_news_admin(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->get(route('news.index'))
            ->assertOk();
    }

    // === The planning is for staff only.

    public function test_a_customer_may_not_reach_the_arrangement_overview(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('arrangement.index'))
            ->assertForbidden();
    }

    public function test_a_customer_lands_on_their_own_dashboard(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('dashboard'))
            ->assertOk();
    }

    public function test_a_user_without_any_role_is_kept_out_of_the_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('dashboard'))
            ->assertForbidden();
    }

    // === Public routes borrow the permissions of the customer role.

    public function test_a_guest_may_open_the_booking_page(): void
    {
        $this->get(route('booking'))->assertOk();
    }

    public function test_a_guest_may_use_the_calculations(): void
    {
        $this->getJson(route('api.calculations.days', [
            'start_date' => now()->addDays(2)->format('Y-m-d'),
            'end_date' => now()->addDays(5)->format('Y-m-d'),
        ]))->assertOk();
    }

    public function test_the_booking_page_closes_when_the_customer_role_loses_the_permission(): void
    {
        Role::findByName('customer')->revokePermissionTo('create booking');

        $this->get(route('booking'))->assertForbidden();
    }

    public function test_a_logged_in_customer_may_still_book(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('booking'))
            ->assertOk();
    }

    // === The location list behind the planning screens.

    public function test_a_customer_may_not_read_the_location_list(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->getJson(route('api.locations'))
            ->assertForbidden();
    }

    public function test_a_receptionist_may_read_the_location_list(): void
    {
        Location::factory()->create(['name' => 'Chalet Boslust']);

        $this->actingAs($this->userWithRole('receptionist'))
            ->getJson(route('api.locations'))
            ->assertOk()
            ->assertJsonFragment(['name' => 'Chalet Boslust']);
    }

    // === Everyone's reservations are staff only.

    public function test_a_customer_may_not_read_the_arrangement_list(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->get(route('api.arrangements.index'))
            ->assertForbidden();
    }

    public function test_a_receptionist_may_read_the_arrangement_list(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->get(route('api.arrangements.index'))
            ->assertOk();
    }

    // === Booking on behalf of a customer at the desk.

    public function test_a_customer_may_not_store_a_customer(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->postJson(route('api.customers.store'), $this->customerPayload())
            ->assertForbidden();
    }

    public function test_a_receptionist_may_store_a_customer(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->postJson(route('api.customers.store'), $this->customerPayload())
            ->assertOk();

        $this->assertDatabaseHas('customers', ['email' => 'nieuwe.klant@example.nl']);
    }

    // === Looking a customer up on e-mail and phone number.

    public function test_a_customer_may_not_look_a_customer_up(): void
    {
        $customer = Customer::factory()->create();

        $this->actingAs($this->userWithRole('customer'))
            ->getJson(route('api.customers.find', [
                'id' => $customer->id,
                'email' => $customer->email,
                'phone_number' => '0612345678',
            ]))
            ->assertForbidden();
    }

    public function test_a_receptionist_may_look_a_customer_up(): void
    {
        // The phone number is deliberately left to the factory: a test that writes
        // its own notation proves the permission, not that the lookup works.
        $customer = Customer::factory()->create([
            'email' => 'sanne.devries@example.nl',
        ]);

        $this->actingAs($this->userWithRole('receptionist'))
            ->getJson(route('api.customers.find', [
                'id' => $customer->id,
                'email' => $customer->email,
                'phone_number' => $customer->phone_number,
            ]))
            ->assertOk()
            ->assertJsonFragment(['email' => 'sanne.devries@example.nl']);
    }

    // === Publishing news.

    public function test_a_customer_may_not_store_news(): void
    {
        $this->actingAs($this->userWithRole('customer'))
            ->postJson(route('api.news.store'), $this->newsPayload())
            ->assertForbidden();
    }

    public function test_a_receptionist_may_store_news(): void
    {
        $this->actingAs($this->userWithRole('receptionist'))
            ->postJson(route('api.news.store'), $this->newsPayload())
            ->assertOk();

        $this->assertDatabaseHas('news', ['title' => 'De speeltuin is vernieuwd']);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function customerPayload(array $overrides = []): array
    {
        return [
            'customer' => array_merge([
                'id' => 0,
                'name' => 'Nieuwe Klant',
                'email' => 'nieuwe.klant@example.nl',
                'phone_number' => '0612345678',
                'street_name' => 'Dorpsstraat',
                'street_number' => '1',
                'postal_code' => '1234 AB',
                'city' => 'Ergens',
                'country' => 'Nederland',
                'create_account' => false,
            ], $overrides),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function newsPayload(): array
    {
        return [
            'title' => 'De speeltuin is vernieuwd',
            'summary' => 'De speeltuin bij de receptie heeft nieuwe toestellen gekregen.',
            'content' => 'Vanaf deze week staan er nieuwe toestellen in de speeltuin bij de receptie.',
            'type' => 'Algemeen',
            'published' => true,
        ];
    }
}
