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
}
