<?php

namespace Tests\Feature;

use App\Models\News;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class NewsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_sees_the_public_news_page(): void
    {
        News::factory(3)->create();

        $response = $this->get(route('news'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('News/Index')
                ->has('news.data', 3)
                ->has('news.links')
        );
    }

    public function test_the_public_page_hides_drafts_and_inactive_articles(): void
    {
        News::factory()->create(['title' => 'Zichtbaar bericht']);
        News::factory()->unpublished()->create();
        News::factory()->create(['status' => 0]);

        $response = $this->get(route('news'));

        $response->assertInertia(
            fn (AssertableInertia $page) => $page
                ->has('news.data', 1)
                ->where('news.data.0.title', 'Zichtbaar bericht')
        );
    }

    public function test_the_dashboard_page_also_lists_drafts(): void
    {
        News::factory(2)->create();
        News::factory()->unpublished()->create();

        $response = $this->actingAs($this->newsEditor())->get(route('news.index'));

        $response->assertOk()->assertInertia(
            fn (AssertableInertia $page) => $page
                ->component('Admin/News/Index')
                ->has('news.data', 3)
        );
    }

    public function test_the_dashboard_page_is_closed_for_users_without_the_permission(): void
    {
        Permission::findOrCreate('access dashboard');

        $user = User::factory()->create();
        $user->givePermissionTo('access dashboard');

        $this->actingAs($user)->get(route('news.index'))->assertForbidden();
    }

    /**
     * Een gebruiker die het nieuwsbeheer in het dashboard mag openen.
     */
    private function newsEditor(): User
    {
        Permission::findOrCreate('access dashboard');
        Permission::findOrCreate('manage news');

        $user = User::factory()->create();
        $user->givePermissionTo(['access dashboard', 'manage news']);

        return $user;
    }
}
