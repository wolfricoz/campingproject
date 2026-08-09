<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmMail;
use App\Mail\GeneralMailMail;
use App\Mail\NewAccountMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\ResetPasswordMail;
use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use App\Models\User;
use Tests\TestCase;

class MailRenderTest extends TestCase
{
    private function makeArrangement(array $attributes = []): Arrangement
    {
        $arrangement = Arrangement::factory()->make(array_merge([
            'guid' => 'test-guid-1234',
            'customer_id' => 1,
            'location_id' => 1,
        ], $attributes));

        $arrangement->setRelation('customer', Customer::factory()->make(['name' => 'Jan Jansen']));
        $arrangement->setRelation('location', Location::factory()->make(['name' => 'Plek A1']));

        return $arrangement;
    }

    public function test_booking_confirm_mail_shows_the_arrangement_details(): void
    {
        $arrangement = $this->makeArrangement();

        $mail = new BookingConfirmMail($arrangement);

        $mail->assertHasSubject('Bevestiging van je boeking');
        $mail->assertSeeInHtml('Jan Jansen', false);
        $mail->assertSeeInHtml('Plek A1', false);
        $mail->assertSeeInHtml('test-guid-1234', false);
        $mail->assertSeeInHtml($arrangement->start_date->format('d-m-Y'), false);
        $mail->assertSeeInHtml($arrangement->end_date->format('d-m-Y'), false);
    }

    public function test_payment_received_mail_shows_the_arrangement_details(): void
    {
        $arrangement = $this->makeArrangement(['total_price' => 1234.50]);

        $mail = new PaymentReceivedMail($arrangement);

        $mail->assertHasSubject('We hebben je betaling ontvangen');
        $mail->assertSeeInHtml('Jan Jansen', false);
        $mail->assertSeeInHtml('1.234,50', false);
    }

    public function test_general_mail_uses_the_given_title_and_text(): void
    {
        $mail = new GeneralMailMail('Onderhoud aan het park', "Beste gast,\nVolgende week is er onderhoud.");

        $mail->assertHasSubject('Onderhoud aan het park');
        $mail->assertSeeInHtml('Onderhoud aan het park', false);
        $mail->assertSeeInHtml('Volgende week is er onderhoud.', false);
        $mail->assertSeeInHtml('Beste gast,<br />', false);
    }

    public function test_general_mail_escapes_html_in_the_text(): void
    {
        $mail = new GeneralMailMail('Test', '<script>alert(1)</script>');

        $mail->assertDontSeeInHtml('<script>alert(1)</script>', false);
    }

    public function test_new_account_mail_contains_a_link_to_set_a_password(): void
    {
        $user = User::factory()->make(['name' => 'Jan Jansen', 'email' => 'jan@example.com']);

        $mail = new NewAccountMail($user, 'test-token-abc');

        $mail->assertHasSubject('Welkom bij '.config('app.name').' - stel je wachtwoord in');
        $mail->assertSeeInHtml('Jan Jansen', false);
        $mail->assertSeeInHtml(route('password.reset', ['token' => 'test-token-abc', 'email' => 'jan@example.com']), false);
    }

    public function test_reset_password_mail_contains_a_link_to_reset_the_password(): void
    {
        $user = User::factory()->make(['name' => 'Jan Jansen', 'email' => 'jan@example.com']);

        $mail = new ResetPasswordMail($user, 'test-token-abc');

        $mail->assertHasSubject('Wachtwoord opnieuw instellen');
        $mail->assertSeeInHtml('Jan Jansen', false);
        $mail->assertSeeInHtml(route('password.reset', ['token' => 'test-token-abc', 'email' => 'jan@example.com']), false);
    }
}
