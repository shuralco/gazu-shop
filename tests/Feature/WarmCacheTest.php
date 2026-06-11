<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WarmCacheTest extends TestCase
{
    public function test_warms_paths_from_sitemap_against_base(): void
    {
        Http::fake([
            '*/sitemap-main.xml' => Http::response('<urlset><url><loc>https://other-host.test/</loc></url></urlset>'),
            '*/sitemap-categories.xml' => Http::response('<urlset><url><loc>https://other-host.test/engine</loc></url><url><loc>https://other-host.test/brakes</loc></url></urlset>'),
            '*/sitemap-brands.xml' => Http::response('<urlset></urlset>'),
            '*' => Http::response('OK', 200),
        ]);

        $this->artisan('cache:warm', ['--base' => 'https://gazu.uno'])
            ->assertSuccessful();

        // шлях узято з <loc>, але хост — із --base (не з sitemap)
        Http::assertSent(fn ($r) => $r->url() === 'https://gazu.uno/engine');
        Http::assertSent(fn ($r) => $r->url() === 'https://gazu.uno/brakes');
        // головна й каталог завжди в наборі
        Http::assertSent(fn ($r) => $r->url() === 'https://gazu.uno/catalog');
        // sitemap товарів НЕ чіпаємо без --products
        Http::assertNotSent(fn ($r) => str_contains($r->url(), 'sitemap-products'));
    }

    public function test_invalid_base_fails(): void
    {
        $this->artisan('cache:warm', ['--base' => 'not-a-url'])->assertFailed();
    }

    public function test_products_flag_includes_products_sitemap(): void
    {
        Http::fake(['*' => Http::response('<urlset></urlset>', 200)]);

        $this->artisan('cache:warm', ['--base' => 'https://gazu.uno', '--products' => true])
            ->assertSuccessful();

        Http::assertSent(fn ($r) => str_contains($r->url(), 'sitemap-products.xml'));
    }
}
