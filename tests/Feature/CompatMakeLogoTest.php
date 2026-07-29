<?php

namespace Tests\Feature;

use App\Filament\Resources\ProductResource;
use App\Models\CarMake;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Емблеми марок у картці товару: у селекті сумісності (адмінка) і
 * згруповані у таблиці сумісності (вітрина).
 */
class CompatMakeLogoTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function optionHtml(string $name): string
    {
        $m = new \ReflectionMethod(ProductResource::class, 'makeOptionHtml');
        $m->setAccessible(true);

        return $m->invoke(null, $name, CarMake::where('name', $name)->first()?->logo_url);
    }

    public function test_make_option_shows_logo(): void
    {
        CarMake::create([
            'name' => 'Volkswagen',
            'slug' => 'volkswagen',
            'is_active' => true,
            'logo_path' => 'https://cdn.example/vw.png',
        ]);
        Cache::flush();

        $html = $this->optionHtml('Volkswagen');

        $this->assertStringContainsString('<img', $html);
        $this->assertStringContainsString('https://cdn.example/vw.png', $html);
        $this->assertStringContainsString('Volkswagen', $html);
    }

    public function test_make_without_logo_falls_back_to_letters(): void
    {
        $html = $this->optionHtml('Невідома');

        $this->assertStringNotContainsString('<img', $html, 'битого <img> бути не повинно');
        $this->assertStringContainsString('Не', $html);
    }

    public function test_option_escapes_untrusted_name(): void
    {
        // ->allowHtml() рендерить опції як розмітку, тож назва з БД мусить бути екранована
        $html = $this->optionHtml('<script>alert(1)</script>');

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function test_storefront_groups_logo_per_make(): void
    {
        $blade = file_get_contents(resource_path('views/gazu/product/v1.blade.php'));

        // Емблема рендериться лише коли марка змінилась…
        $this->assertStringContainsString('$newMake', $blade);
        // …а рядки попередньо відсортовані, інакше групування безглузде
        $this->assertStringContainsString('usort($compat', $blade);
    }
}
