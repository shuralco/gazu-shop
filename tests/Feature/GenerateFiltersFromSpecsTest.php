<?php

namespace Tests\Feature;

use App\Models\FilterGroup;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * filters:generate-from-specs — spec-ключі → таксономія фільтрів.
 * Головне: ключі-ідентифікатори (артикул, крос-код) не мають ставати фільтрами.
 */
class GenerateFiltersFromSpecsTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function product(array $specs): Product
    {
        return Product::factory()->create(['is_active' => true, 'specifications' => $specs]);
    }

    private function groupTitles(): array
    {
        return FilterGroup::query()->pluck('title')->sort()->values()->all();
    }

    public function test_real_attribute_becomes_a_filter_group(): void
    {
        $this->product(['Тип матеріалу' => 'Папір']);
        $this->product(['Тип матеріалу' => 'Вугілля']);
        $this->product(['Тип матеріалу' => 'Папір']);

        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertEquals(['Тип матеріалу'], $this->groupTitles());
        $this->assertSame(3, DB::table('filter_products')->count());
    }

    public function test_identifier_like_key_is_rejected_by_cardinality(): void
    {
        // 6 товарів, 6 різних значень → ідентифікатор, а не характеристика.
        foreach (range(1, 6) as $i) {
            $this->product(['Код виробника' => "ABC-{$i}"]);
        }

        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertSame([], $this->groupTitles(), 'майже унікальні значення не мають ставати фільтром');
        $this->assertSame(0, DB::table('filter_products')->count());
    }

    public function test_identifier_key_is_rejected_by_name_even_with_low_cardinality(): void
    {
        $this->product(['Крос-код (OEM)' => 'AAA']);
        $this->product(['Крос-код (OEM)' => 'AAA']);
        $this->product(['Артикул' => 'BBB']);
        $this->product(['Артикул' => 'BBB']);

        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertSame([], $this->groupTitles(), 'крос-код/артикул відсікаються за назвою ключа');
    }

    public function test_key_present_on_single_product_is_skipped(): void
    {
        $this->product(['Колір' => 'Синій']);

        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertSame([], $this->groupTitles(), 'один товар — фільтрувати нема чого');
    }

    public function test_key_with_one_value_for_everyone_is_skipped(): void
    {
        $this->product(['Гарантія' => '12 місяців']);
        $this->product(['Гарантія' => '12 місяців']);
        $this->product(['Гарантія' => '12 місяців']);

        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertSame([], $this->groupTitles(), 'фільтр, що нічого не звужує, марний');
    }

    public function test_mixed_catalog_keeps_only_useful_keys(): void
    {
        foreach (range(1, 6) as $i) {
            $this->product([
                'Тип матеріалу' => $i % 2 ? 'Папір' : 'Вугілля',
                'Артикул' => "SKU-{$i}",
                'Серійний номер' => "SN-{$i}",
            ]);
        }

        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertEquals(['Тип матеріалу'], $this->groupTitles());
    }

    public function test_dry_run_writes_nothing(): void
    {
        $this->product(['Тип матеріалу' => 'Папір']);
        $this->product(['Тип матеріалу' => 'Вугілля']);

        $this->artisan('filters:generate-from-specs', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame([], $this->groupTitles());
        $this->assertSame(0, DB::table('filter_products')->count());
    }

    public function test_rerun_is_idempotent(): void
    {
        $this->product(['Тип матеріалу' => 'Папір']);
        $this->product(['Тип матеріалу' => 'Вугілля']);

        $this->artisan('filters:generate-from-specs')->assertSuccessful();
        $this->artisan('filters:generate-from-specs')->assertSuccessful();

        $this->assertSame(1, FilterGroup::count());
        $this->assertSame(2, DB::table('filter_products')->count());
    }
}
