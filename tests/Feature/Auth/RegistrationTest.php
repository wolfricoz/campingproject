<?php

namespace Tests\Feature\Auth;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    public function test_a_new_user_becomes_a_customer(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $user = User::where('email', 'test@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
        $this->assertTrue($user->hasPermissionTo('create booking'));
        $this->assertFalse($user->hasPermissionTo('access dashboard'));
    }

    public function test_an_account_created_during_a_booking_becomes_a_customer(): void
    {
        Mail::fake();

        Customer::createNewCustomer([
            'name' => 'Jan Jansen',
            'email' => 'jan@example.com',
            'phone_number' => '0612345678',
            'street_name' => 'Dorpsstraat',
            'street_number' => '1',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'create_account' => true,
        ]);

        $user = User::where('email', 'jan@example.com')->firstOrFail();

        $this->assertTrue($user->hasRole('customer'));
    }
}
