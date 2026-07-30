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
}
