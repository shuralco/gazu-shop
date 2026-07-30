<?php

namespace Tests\Feature;

use App\Models\Brand;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Лого бренду, завантажене в адмінці, мусить бути видно на вітрині.
 * Раніше і сторінка /brand, і смужка брендів виводили лише назву —
 * тега <img> у шаблонах не було взагалі.
 */
class BrandLogoVisibleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_brand_list_shows_uploaded_logo(): void
    {
        Brand::create([
            'name' => 'Тест-Бренд',
            'slug' => 'test-brand',
            'is_active' => true,
            'logo' => 'https://cdn.example/brand.png',
        ]);

        $this->get('/brand')
            ->assertOk()
            ->assertSee('https://cdn.example/brand.png')
            ->assertSee('Тест-Бренд');
    }

    public function test_brand_without_logo_falls_back_to_name(): void
    {
        Brand::create(['name' => 'Без Лого', 'slug' => 'bez-logo', 'is_active' => true]);

        $html = $this->get('/brand')->assertOk()->getContent();

        $this->assertStringContainsString('Без Лого', $html);
        // жодного битого <img> для цього бренду
        $this->assertStringNotContainsString('alt="Без Лого"', $html);
    }

    public function test_model_is_never_rendered_as_json(): void
    {
        Brand::create(['name' => 'Модель', 'slug' => 'model-brand', 'is_active' => true]);

        $html = $this->get('/brand')->assertOk()->getContent();

        // Раніше (string) $model давав JSON прямо в плитку меню
        $this->assertStringNotContainsString('&quot;meta_title&quot;', $html);
        $this->assertStringNotContainsString('"meta_title"', $html);
    }

    public function test_missing_file_does_not_produce_broken_image(): void
    {
        Brand::create([
            'name' => 'Зникло',
            'slug' => 'znyklo',
            'is_active' => true,
            'logo' => 'brands/logos/nemaye.png', // файлу не існує
        ]);

        $html = $this->get('/brand')->assertOk()->getContent();

        $this->assertStringNotContainsString('src="'.url('/storage/brands/logos/nemaye.png').'"', $html, 'битий <img> показувати не можна');
        $this->assertStringContainsString('Зникло', $html);
    }

    public function test_homepage_strip_gets_logo_from_builder(): void
    {
        Brand::create([
            'name' => 'Стрічка',
            'slug' => 'strichka',
            'is_active' => true,
            'logo' => 'https://cdn.example/strip.png',
        ]);

        $list = app(\App\Services\Gazu\MegaMenuBuilder::class)->brands();

        $row = collect($list)->firstWhere('slug', 'strichka');
        $this->assertNotNull($row, 'бренд мусить бути у списку для меню/смужки');
        $this->assertSame('https://cdn.example/strip.png', $row['logo'] ?? null);
    }

    public function test_saving_brand_flushes_menu_cache(): void
    {
        \Illuminate\Support\Facades\Cache::put('gazu:megabrands', [['name' => 'старе', 'slug' => 'stare']], 3600);

        Brand::create(['name' => 'Нове', 'slug' => 'nove', 'is_active' => true]);

        $this->assertNull(
            \Illuminate\Support\Facades\Cache::get('gazu:megabrands'),
            'без скидання кешу завантажене лого зʼявлялось би лише після TTL'
        );
    }

    public function test_brand_falls_back_to_car_make_logo(): void
    {
        // Клієнт заливає емблеми в «Марки авто»; бренди — окрема сутність
        // із тими самими назвами. Плитка бренду мусить показати ту емблему.
        \App\Models\CarMake::create([
            'name' => 'Volkswagen',
            'slug' => 'volkswagen',
            'is_active' => true,
            'logo_path' => 'https://cdn.example/vw-make.png',
        ]);
        \Illuminate\Support\Facades\Cache::flush();

        $faw = Brand::create(['name' => 'VW-FAW', 'slug' => 'vw-faw', 'is_active' => true]);
        $after = Brand::create(['name' => 'BYD Aftermarket', 'slug' => 'byd-after', 'is_active' => true]);

        $this->assertSame('https://cdn.example/vw-make.png', $faw->logo_url, 'VW-FAW → емблема Volkswagen');
        // BYD марки немає в БД, але є офіційний файл у репо
        $this->assertStringContainsString('byd', (string) $after->logo_url);
    }

    public function test_own_logo_wins_over_make(): void
    {
        \App\Models\CarMake::create([
            'name' => 'Tesla', 'slug' => 'tesla', 'is_active' => true,
            'logo_path' => 'https://cdn.example/make.png',
        ]);
        \Illuminate\Support\Facades\Cache::flush();

        $b = Brand::create([
            'name' => 'Tesla', 'slug' => 'tesla-brand', 'is_active' => true,
            'logo' => 'https://cdn.example/own.png',
        ]);

        $this->assertSame('https://cdn.example/own.png', $b->logo_url);
    }

    public function test_unknown_brand_has_no_logo(): void
    {
        $b = Brand::create(['name' => 'Xiaomi', 'slug' => 'xiaomi', 'is_active' => true]);

        $this->assertNull($b->logo_url, 'без емблеми — шаблон покаже назву');
    }

    public function test_brand_upload_uses_public_disk(): void
    {
        // Типовий диск на проді — local: без disk('public') файл ліг би
        // у storage/app/private (не віддається вебом, стирається деплоєм).
        $src = file_get_contents(app_path('Filament/Resources/BrandResource.php'));
        $i = strpos($src, "FileUpload::make('logo')");
        $this->assertNotFalse($i);
        // вікно з запасом: коментар українською — це байти, не символи
        $this->assertStringContainsString("->disk('public')", substr($src, $i, 900));
    }
}
