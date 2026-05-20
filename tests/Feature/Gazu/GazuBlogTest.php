<?php

namespace Tests\Feature\Gazu;

use App\Models\BlogCategory;
use App\Models\Page;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GazuBlogTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function makePost(array $attrs = []): Page
    {
        return Page::create(array_merge([
            'title' => 'Тестова стаття',
            'slug' => 'test-post-'.uniqid(),
            'content' => str_repeat('слово ', 500),
            'excerpt' => 'Анонс',
            'template' => 'blog_post',
            'is_active' => true,
        ], $attrs));
    }

    public function test_blog_schema_present(): void
    {
        $this->assertTrue(Schema::hasTable('blog_categories'));
        foreach (['blog_category_id', 'author', 'published_at', 'views', 'is_featured'] as $col) {
            $this->assertTrue(Schema::hasColumn('pages', $col), "pages.$col missing");
        }
    }

    public function test_blog_category_filters_posts(): void
    {
        $cat = BlogCategory::create(['name' => 'Гайди', 'slug' => 'guides', 'is_active' => true]);
        $inCat = $this->makePost(['title' => 'У рубриці', 'blog_category_id' => $cat->id]);
        $other = $this->makePost(['title' => 'Поза рубрикою']);

        $this->get('/blog/rubryka/guides')
            ->assertStatus(200)
            ->assertSee('У рубриці', false)
            ->assertDontSee('Поза рубрикою', false);
    }

    public function test_reading_time_accessor(): void
    {
        $post = $this->makePost(['content' => str_repeat('слово ', 400)]);
        $this->assertSame(2, $post->reading_minutes); // 400/200 = 2
    }

    public function test_post_view_increments(): void
    {
        $post = $this->makePost(['slug' => 'viewed-post', 'views' => 0]);
        $this->get('/blog/viewed-post')->assertStatus(200);
        $this->assertSame(1, $post->fresh()->views);
    }

    public function test_blog_index_lists_posts(): void
    {
        $this->makePost(['title' => 'Стаття на головній блогу']);
        $this->get('/blog')->assertStatus(200)->assertSee('Стаття на головній блогу', false);
    }
}
