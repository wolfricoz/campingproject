<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * De taalkeuze wordt in de sessie bewaard, door SetLocale toegepast en via Inertia
 * als 'locale' en 'translations' gedeeld met de front-end.
 */
class LocaleSwitchTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_remembers_the_chosen_locale_in_the_session(): void
    {
        $response = $this->from(route('home'))->post(route('locale.update', 'en'));

        $response->assertRedirect(route('home'));
        $this->assertSame('en', session('locale'));
    }

    public function test_it_rejects_a_locale_that_is_not_available(): void
    {
        $this->post(route('locale.update', 'de'))->assertNotFound();

        $this->assertNull(session('locale'));
    }

    public function test_it_shares_the_dutch_source_texts_without_a_translation_file(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'nl')
                ->where('translations', [])
            );
    }

    public function test_it_shares_the_english_translations_after_switching(): void
    {
        $this->post(route('locale.update', 'en'));

        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('locale', 'en')
                ->where('translations.Locaties', 'Locations')
            );
    }
}
