<?php

namespace Tests\Feature;

use App\Enums\PaymentMethod;
use App\Mail\PaymentReceivedMail;
use App\Models\Arrangement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PaymentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_unpaid_arrangement_shows_the_payment_page(): void
    {
        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $response = $this->get(route('payment', ['guid' => $arrangement->guid]));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Payment')
                ->where('guid', $arrangement->guid)
        );
    }

    public function test_an_unknown_guid_returns_a_not_found(): void
    {
        $this->get(route('payment', ['guid' => 'bestaat-niet']))->assertNotFound();
    }

    public function test_a_paid_arrangement_can_not_open_the_payment_page_again(): void
    {
        $arrangement = Arrangement::factory()->create(['payment_received' => true]);

        $this->get(route('payment', ['guid' => $arrangement->guid]))
            ->assertRedirect(route('home'));
    }

    public function test_a_payment_marks_the_arrangement_as_paid_and_sends_a_mail(): void
    {
        Mail::fake();

        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $this->post(route('payment.complete'), ['guid' => $arrangement->guid, 'payment_method' => 'ideal'])
            ->assertRedirect(route('home'));

        $this->assertTrue($arrangement->fresh()->payment_received);

        Mail::assertQueued(PaymentReceivedMail::class);
    }

    public function test_a_second_payment_is_refused_and_sends_no_extra_mail(): void
    {
        Mail::fake();

        $arrangement = Arrangement::factory()->create(['payment_received' => true]);

        $this->post(route('payment.complete'), ['guid' => $arrangement->guid, 'payment_method' => 'ideal'])
            ->assertRedirect(route('home'));

        Mail::assertNothingSent();
    }

    public function test_a_payment_for_an_unknown_guid_returns_a_not_found(): void
    {
        $this->post(route('payment.complete'), ['guid' => 'bestaat-niet', 'payment_method' => 'ideal'])
            ->assertNotFound();
    }

    public function test_the_chosen_payment_method_and_the_moment_of_receipt_are_stored(): void
    {
        Mail::fake();
        $this->freezeTime();

        $arrangement = Arrangement::factory()->create([
            'payment_received' => false,
            'payment_method' => null,
            'payment_received_at' => null,
        ]);

        $this->post(route('payment.complete'), [
            'guid' => $arrangement->guid,
            'payment_method' => 'bank_transfer',
        ])->assertRedirect(route('home'));

        $arrangement->refresh();

        $this->assertSame(PaymentMethod::BANK_TRANSFER, $arrangement->payment_method);
        $this->assertTrue($arrangement->payment_received);
        $this->assertNotNull($arrangement->payment_received_at);
        $this->assertSame(
            now()->format('Y-m-d H:i:s'),
            $arrangement->payment_received_at->format('Y-m-d H:i:s'),
        );
    }

    public function test_a_payment_without_a_method_is_refused(): void
    {
        Mail::fake();

        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $this->post(route('payment.complete'), ['guid' => $arrangement->guid])
            ->assertSessionHasErrors('payment_method');

        $this->assertFalse($arrangement->fresh()->payment_received);
        Mail::assertNothingQueued();
    }

    public function test_a_payment_method_we_do_not_offer_is_refused(): void
    {
        Mail::fake();

        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $this->post(route('payment.complete'), [
            'guid' => $arrangement->guid,
            'payment_method' => 'contant',
        ])->assertSessionHasErrors('payment_method');

        $this->assertFalse($arrangement->fresh()->payment_received);
        Mail::assertNothingQueued();
    }

    public function test_a_second_payment_does_not_overwrite_the_first_method(): void
    {
        Mail::fake();

        $arrangement = Arrangement::factory()->create([
            'payment_received' => true,
            'payment_method' => PaymentMethod::IDEAL,
        ]);

        $this->post(route('payment.complete'), [
            'guid' => $arrangement->guid,
            'payment_method' => 'bank_transfer',
        ])->assertRedirect(route('home'));

        $this->assertSame(PaymentMethod::IDEAL, $arrangement->fresh()->payment_method);
    }
}
