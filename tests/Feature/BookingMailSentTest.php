<?php

namespace Tests\Feature;

use App\Mail\BookingConfirmMail;
use App\Mail\BookingNotificationMail;
use App\Models\Arrangement;
use App\Models\Customer;
use App\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class BookingMailSentTest extends TestCase
{
    use RefreshDatabase;

    private function inDays(int $days): string
    {
        return now()->addDays($days)->format('Y-m-d\TH:i');
    }

    /**
     * @return array<string, mixed>
     */
    private function bookingPayload(Location $location, array $overrides = []): array
    {
        return [
            'location_id' => $location->id,
            'start_date' => $this->inDays(10),
            'end_date' => $this->inDays(14),
            'customer' => array_merge([
                'name' => 'Jan Jansen',
                'email' => 'Jan@Voorbeeld.nl',
                'phone_number' => '06 12345678',
                'street_name' => 'Dorpsstraat',
                'street_number' => '1',
                'postal_code' => '1234 AB',
                'city' => 'Ergens',
                'country' => 'Nederland',
                'create_account' => false,
            ], $overrides),
        ];
    }

    public function test_a_booking_sends_a_confirmation_mail_to_the_customer(): void
    {
        Mail::fake();

        $location = Location::factory()->create();

        $this->post(route('booking.store'), $this->bookingPayload($location))
            ->assertSessionHasNoErrors();

        $arrangement = Arrangement::firstOrFail();

        Mail::assertQueued(BookingConfirmMail::class, function (BookingConfirmMail $mail) use ($arrangement) {
            return $mail->hasTo('jan@voorbeeld.nl')
                && $mail->arrangement->id === $arrangement->id;
        });
    }

    public function test_a_booking_sends_a_notification_mail_to_the_site_administrator(): void
    {
        Mail::fake();

        $location = Location::factory()->create();

        $this->post(route('booking.store'), $this->bookingPayload($location))
            ->assertSessionHasNoErrors();

        $arrangement = Arrangement::firstOrFail();

        Mail::assertQueued(BookingNotificationMail::class, function (BookingNotificationMail $mail) use ($arrangement) {
            return $mail->hasTo(config('mail.contact_email'))
                && $mail->arrangement->id === $arrangement->id;
        });
    }

    public function test_a_booking_marks_the_confirmation_mail_as_sent(): void
    {
        Mail::fake();

        $location = Location::factory()->create();

        $this->post(route('booking.store'), $this->bookingPayload($location))
            ->assertSessionHasNoErrors();

        $this->assertTrue(Arrangement::firstOrFail()->confirmation_email_sent);
    }

    public function test_a_refused_booking_sends_no_mail(): void
    {
        $location = Location::factory()->create();
        Arrangement::factory()->create([
            'location_id' => $location->id,
            'start_date' => now()->addDays(12)->format('Y-m-d H:i:s'),
            'end_date' => now()->addDays(16)->format('Y-m-d H:i:s'),
            'booking_status' => 'confirmed',
        ]);

        Mail::fake();

        $this->post(route('booking.store'), $this->bookingPayload($location))
            ->assertSessionHasErrors('location_id');

        Mail::assertNothingSent();
    }

    public function test_a_failing_mail_does_not_break_the_booking(): void
    {
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('mailserver onbereikbaar'));

        $location = Location::factory()->create();

        $this->post(route('booking.store'), $this->bookingPayload($location))
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('payment', ['guid' => Arrangement::firstOrFail()->guid]));

        $this->assertFalse(Arrangement::firstOrFail()->confirmation_email_sent);
    }

    public function test_the_booking_notification_mail_shows_the_customer_details(): void
    {
        $arrangement = Arrangement::factory()->make([
            'guid' => 'test-guid-1234',
            'customer_id' => 1,
            'location_id' => 1,
            'total_price' => 1234.50,
        ]);

        $arrangement->setRelation('customer', Customer::factory()->make([
            'name' => 'Jan Jansen',
            'email' => 'jan@voorbeeld.nl',
            'phone_number' => '0612345678',
        ]));
        $arrangement->setRelation('location', Location::factory()->make(['name' => 'Plek A1']));

        $mail = new BookingNotificationMail($arrangement);

        $mail->assertHasSubject('Nieuwe boeking ontvangen');
        $mail->assertSeeInHtml('Jan Jansen', false);
        $mail->assertSeeInHtml('jan@voorbeeld.nl', false);
        $mail->assertSeeInHtml('0612345678', false);
        $mail->assertSeeInHtml('Plek A1', false);
        $mail->assertSeeInHtml('test-guid-1234', false);
        $mail->assertSeeInHtml('1.234,50', false);
    }
}
