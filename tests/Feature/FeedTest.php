<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Services\FeedGenerator\YmlFeedGenerator;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FeedTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_google_feed_generates_xml(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => true, 'category_id' => $category->id]);

        $generator = app(YmlFeedGenerator::class);
        $xml = $generator->generate('google');

        $this->assertStringContainsString('<?xml', $xml);
    }

    public function test_rozetka_feed_generates_xml(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => true, 'category_id' => $category->id]);

        $generator = app(YmlFeedGenerator::class);
        $xml = $generator->generate('rozetka');

        $this->assertStringContainsString('<?xml', $xml);
    }

    public function test_prom_feed_generates_xml(): void
    {
        $category = Category::factory()->create(['is_active' => true]);
        Product::factory()->create(['is_active' => true, 'category_id' => $category->id]);

        $generator = app(YmlFeedGenerator::class);
        $xml = $generator->generate('prom');

        $this->assertStringContainsString('<?xml', $xml);
    }

    public function test_empty_feed_generates_valid_xml(): void
    {
        $generator = app(YmlFeedGenerator::class);
        $xml = $generator->generate('google');

        $this->assertStringContainsString('<?xml', $xml);
    }
}
