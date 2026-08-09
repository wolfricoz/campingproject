<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Het contactformulier op de publieke site. Het versturen van het bericht moet nog
 * gebouwd worden; deze test dekt de route en de validatie.
 */
class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_can_submit_the_contact_form(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'email' => 'kampeerder@voorbeeld.nl',
            'title' => 'Vraag over de openingstijden',
            'message' => 'Tot hoe laat kan ik aankomen?',
        ]);

        $response->assertRedirect(route('contact'))->assertSessionHasNoErrors();
    }

    public function test_it_requires_all_three_fields(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.store'), []);

        $response->assertSessionHasErrors(['email', 'title', 'message']);
    }

    public function test_it_rejects_an_invalid_email_address(): void
    {
        $response = $this->from(route('contact'))->post(route('contact.store'), [
            'email' => 'geen-adres',
            'title' => 'Vraag',
            'message' => 'Bericht',
        ]);

        $response->assertSessionHasErrors('email');
    }
}
