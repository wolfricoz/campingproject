<?php

namespace Tests\Feature;

use App\Mail\PaymentReceivedMail;
use App\Models\Arrangement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class ArrangementPaymentConfirmationTest extends TestCase
{
    use RefreshDatabase;

    private function receptionist(): User
    {
        return User::factory()->create()->assignRole('receptionist');
    }

    private function confirmPayment(Arrangement $arrangement): TestResponse
    {
        return $this->actingAs($this->receptionist())->postJson(route('api.arrangements.payment'), [
            'id' => $arrangement->id,
        ]);
    }

    public function test_it_registers_the_payment(): void
    {
        Mail::fake();
        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $response = $this->confirmPayment($arrangement);

        $response->assertOk()->assertJsonPath('updated_data.payment_received', true);
        $this->assertTrue($arrangement->fresh()->payment_received);
    }

    public function test_it_mails_the_customer_that_the_payment_came_in(): void
    {
        Mail::fake();
        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $this->confirmPayment($arrangement)->assertOk();

        Mail::assertQueued(PaymentReceivedMail::class);
    }

    /**
     * Confirming the payment says nothing about the reservation itself.
     */
    public function test_it_leaves_the_booking_status_alone(): void
    {
        Mail::fake();
        $arrangement = Arrangement::factory()->create([
            'booking_status' => 'pending',
            'payment_received' => false,
        ]);

        $this->confirmPayment($arrangement)->assertOk();

        $this->assertSame('pending', $arrangement->fresh()->booking_status);
    }

    public function test_a_second_confirmation_does_not_mail_the_customer_again(): void
    {
        Mail::fake();
        $arrangement = Arrangement::factory()->create(['payment_received' => true]);

        $this->confirmPayment($arrangement)->assertOk();

        Mail::assertNothingQueued();
    }

    public function test_it_refuses_an_arrangement_that_does_not_exist(): void
    {
        $response = $this->actingAs($this->receptionist())->postJson(route('api.arrangements.payment'), [
            'id' => 9999,
        ]);

        $response->assertStatus(422)->assertJsonValidationErrors('id');
    }

    public function test_a_guest_may_not_confirm_a_payment(): void
    {
        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $this->postJson(route('api.arrangements.payment'), ['id' => $arrangement->id])
            ->assertStatus(401);

        $this->assertFalse($arrangement->fresh()->payment_received);
    }

    public function test_a_customer_may_not_confirm_a_payment(): void
    {
        $arrangement = Arrangement::factory()->create(['payment_received' => false]);

        $this->actingAs(User::factory()->create()->assignRole('customer'))
            ->postJson(route('api.arrangements.payment'), ['id' => $arrangement->id])
            ->assertForbidden();

        $this->assertFalse($arrangement->fresh()->payment_received);
    }
}
