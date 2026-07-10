<?php

namespace Tests\Feature;

use App\Console\Commands\FiltersFromTitles;
use App\Models\FilterGroup;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * gazu:filters-from-titles — розпізнавання характеристик у назвах.
 * Назви взяті з живого каталогу gazu.uno.
 */
class FiltersFromTitlesTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function detect(string $title): array
    {
        return app(FiltersFromTitles::class)->detect($title);
    }

    public function test_detects_copy_and_original(): void
    {
        $this->assertSame('Копія', $this->detect('Комлект щіток склоочисника ID 3 КОПІЯ')['Тип запчастини']);
        $this->assertSame('Оригінал', $this->detect('Трансмісійне олива Tesla Оригінал 946мл')['Тип запчастини']);
    }

    public function test_copy_wins_over_original_when_both_present(): void
    {
        // «КОПІЯ біле пакування» + слово «Оригінал» у назві бренду не має зробити товар оригіналом.
        $this->assertSame('Копія', $this->detect('Фільтр оливи Оригінал упаковка КОПІЯ')['Тип запчастини']);
    }

    public function test_detects_volume_in_litres_and_millilitres(): void
    {
        $this->assertSame('946 мл', $this->detect('Трансмісійне олива Tesla Оригінал 946мл')['Обʼєм']);
        $this->assertSame('3.5 л', $this->detect('Фіолетовий антифриз для батареї 3.5л BYD')['Обʼєм']);
        $this->assertSame('1 л', $this->detect('Гальмівна рідина HZY6 1л')['Обʼєм']);
    }

    public function test_detects_mounting_place(): void
    {
        $this->assertSame('Салон', $this->detect('Фільтр повітря в салоні')['Місце встановлення']);
        $this->assertSame('Салон', $this->detect('Фільтр салона для Xiaomi SU7/YU7')['Місце встановлення']);
        $this->assertSame('Під капотом', $this->detect('HEPA фільтр повітряний (Великий під капотом) model S')['Місце встановлення']);
    }

    public function test_ignores_titles_without_attributes(): void
    {
        $this->assertSame([], $this->detect('Пробка зливна/заливна магнітна'));
        $this->assertSame([], $this->detect('Ущільнення зливного/заливного гвинта для редуктора'));
    }

    public function test_model_names_do_not_leak_into_volume(): void
    {
        // «ID 4», «model X», «G12», «SU7» — не обʼєми.
        foreach (['Комлект щіток склоочисника ID 4, etron Q4 КОПІЯ', 'Фільтр повітря в салоні Tesla model X'] as $t) {
            $this->assertArrayNotHasKey('Обʼєм', $this->detect($t), "не має бути обʼєму в: $t");
        }
    }

    public function test_command_writes_filters_and_is_idempotent(): void
    {
        Product::factory()->create(['is_active' => true, 'title' => 'Гальмівна рідина HZY6 1л']);
        Product::factory()->create(['is_active' => true, 'title' => 'Фіолетовий антифриз для батареї 3.5л BYD']);
        Product::factory()->create(['is_active' => true, 'title' => 'Фільтр повітря в салоні']);

        $this->artisan('gazu:filters-from-titles')->assertSuccessful();
        $this->artisan('gazu:filters-from-titles')->assertSuccessful();

        $this->assertEqualsCanonicalizing(
            ['Обʼєм', 'Місце встановлення'],
            FilterGroup::pluck('title')->all()
        );
        $this->assertSame(3, DB::table('filter_products')->count(), 'повторний запуск не дублює звʼязки');
    }

    public function test_dry_run_writes_nothing(): void
    {
        Product::factory()->create(['is_active' => true, 'title' => 'Гальмівна рідина HZY6 1л']);

        $this->artisan('gazu:filters-from-titles', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(0, FilterGroup::count());
        $this->assertSame(0, DB::table('filter_products')->count());
    }
}
