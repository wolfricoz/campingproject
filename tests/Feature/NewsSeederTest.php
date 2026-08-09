<?php

namespace Tests\Feature;

use App\Models\News;
use Database\Seeders\NewsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NewsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_published_and_unpublished_news(): void
    {
        $this->seed(NewsSeeder::class);

        $this->assertSame(12, News::count());
        $this->assertSame(10, News::where('published', true)->count());
        $this->assertSame(2, News::where('published', false)->count());
    }

    public function test_every_seeded_article_is_complete(): void
    {
        $this->seed(NewsSeeder::class);

        foreach (News::all() as $article) {
            $this->assertTrue(Str::isUuid($article->guid), 'Nieuwsbericht kreeg geen geldige uuid als guid.');
            $this->assertNotEmpty($article->title);
            $this->assertSame(Str::slug($article->title), $article->slug);
            $this->assertNotEmpty($article->content);
            $this->assertSame(1, $article->status);
        }
    }

    public function test_the_fixed_articles_are_seeded_newest_first(): void
    {
        $this->seed(NewsSeeder::class);

        $article = News::where('slug', 'het-nieuwe-seizoen-is-geopend')->firstOrFail();

        $older = News::where('slug', 'vroegboekkorting-15-op-alle-plaatsen')->firstOrFail();

        $this->assertSame('Algemeen', $article->type);
        $this->assertTrue((bool) $article->published);
        $this->assertTrue(
            $article->created_at->greaterThan($older->created_at),
            'Het eerste vaste bericht hoort het nieuwst te zijn.'
        );
    }
}
