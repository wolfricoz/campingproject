<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * De routes gebruiken de 'permission:' en 'role:' aliassen van spatie/laravel-permission.
 * Sinds Laravel 11 registreert het framework die niet meer zelf, dus zonder alias in
 * bootstrap/app.php gooit elke route een "Target class [permission] does not exist".
 */
class PermissionMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_permission_alias_resolves_and_lets_an_authorised_user_through(): void
    {
        Permission::findOrCreate('access dashboard');

        $user = User::factory()->create();
        $user->givePermissionTo('access dashboard');

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
    }

    public function test_the_permission_alias_blocks_a_user_without_the_permission(): void
    {
        Permission::findOrCreate('access dashboard');

        $response = $this->actingAs(User::factory()->create())->get(route('dashboard'));

        $response->assertForbidden();
    }
}
