<?php

namespace Tests\Feature;

use App\Mail\NewAccountMail;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class NewAccountMailSentTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, mixed>
     */
    private function customerData(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Jan Jansen',
            'email' => 'jan@example.com',
            'phone_number' => '0612345678',
            'street_name' => 'Dorpsstraat',
            'street_number' => '1',
            'postal_code' => '1234AB',
            'city' => 'Amsterdam',
            'country' => 'Nederland',
            'create_account' => true,
        ], $overrides);
    }

    public function test_a_new_account_mail_is_sent_when_an_account_is_created(): void
    {
        Mail::fake();

        Customer::createNewCustomer($this->customerData());

        Mail::assertSent(NewAccountMail::class, function (NewAccountMail $mail) {
            return $mail->hasTo('jan@example.com')
                && $mail->user->email === 'jan@example.com'
                && $mail->token !== '';
        });
    }

    public function test_no_mail_is_sent_when_the_customer_does_not_want_an_account(): void
    {
        Mail::fake();

        Customer::createNewCustomer($this->customerData(['create_account' => false]));

        Mail::assertNothingSent();
    }

    public function test_no_mail_is_sent_when_the_user_already_exists(): void
    {
        User::factory()->create(['email' => 'jan@example.com']);

        Mail::fake();

        Customer::createNewCustomer($this->customerData());

        Mail::assertNothingSent();
    }
}
