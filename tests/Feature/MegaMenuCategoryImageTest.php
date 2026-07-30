<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DisplaySetting;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Зображення категорії, завантажене в адмінці, мусить доходити до плиток
 * на головній — у тому числі коли меню збудоване в редакторі структури.
 *
 * Раніше в тій гілці 'image' був жорстко null, тож завантаження в адмінці
 * ні на що не впливало.
 */
class MegaMenuCategoryImageTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function build(): array
    {
        Cache::flush();

        return app(\App\Services\Gazu\MegaMenuBuilder::class)->build();
    }

    private function editorStructure(Category $cat): void
    {
        DisplaySetting::set('main_mega_menu_structure', json_encode([
            'columns' => [[
                ['type' => 'category', 'title' => 'Оливи', 'slug' => 'olyvy', 'category_id' => $cat->id, 'children' => []],
            ]],
        ]));
    }

    public function test_uploaded_image_reaches_menu_from_editor_structure(): void
    {
        $cat = Category::create(['slug' => ['uk' => 'olyvy'], 'title' => ['uk' => 'Оливи'], 'is_active' => true]);

        // Зовнішній URL — щоб тест не залежав від symlink public/storage,
        // якого локально немає (він указує в шлях контейнера).
        $cat->update(['image' => 'https://cdn.example/test-olyvy.png']);

        $this->editorStructure($cat);

        $node = collect($this->build())->firstWhere('slug', 'olyvy');

        $this->assertNotNull($node, 'категорія має бути в меню');
        $this->assertNotNull($node['image'], 'завантажене фото мусить дійти до плитки');
        $this->assertStringContainsString('test-olyvy.png', $node['image']);
    }

    public function test_missing_file_yields_null_not_broken_image(): void
    {
        $cat = Category::create(['slug' => ['uk' => 'filtry'], 'title' => ['uk' => 'Фільтри'], 'is_active' => true]);
        $cat->update(['image' => 'categories/znyklo.png']); // файлу немає

        $this->editorStructure($cat);
        DisplaySetting::set('main_mega_menu_structure', json_encode([
            'columns' => [[
                ['type' => 'category', 'title' => 'Фільтри', 'slug' => 'filtry', 'category_id' => $cat->id, 'children' => []],
            ]],
        ]));

        $node = collect($this->build())->firstWhere('slug', 'filtry');

        $this->assertNotNull($node);
        $this->assertNull($node['image'], 'зниклий файл → null, а не битий <img>');
    }
}
