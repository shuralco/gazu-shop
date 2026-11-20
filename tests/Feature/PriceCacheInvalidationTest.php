<?php

namespace Tests\Feature;

use App\Models\CustomerGroup;
use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Spatie\ResponseCache\Facades\ResponseCache;
use Tests\TestCase;

/**
 * Зміна ціни/націнки має миттєво доходити до сайту: раніше масові правки
 * (batch editor — прямі SQL-запити без подій моделі) і зміна націнки групи
 * не скидали HTML-кеш (TTL 7 днів) → «ціни не реагують на перецінку».
 */
class PriceCacheInvalidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_product_price_change_flushes_whole_storefront_cache(): void
    {
        $p = Product::factory()->create(['is_active' => true, 'price' => 100]);

        ResponseCache::shouldReceive('clear')->atLeast()->once();
        ResponseCache::shouldReceive('forget')->zeroOrMoreTimes();

        $p->update(['price' => 150]);
    }

    public function test_non_price_edit_stays_scoped(): void
    {
        $p = Product::factory()->create(['is_active' => true]);

        ResponseCache::shouldReceive('clear')->never();
        ResponseCache::shouldReceive('forget')->zeroOrMoreTimes();

        $p->update(['sku' => 'NEW-SKU-'.uniqid()]);
    }

    public function test_group_markup_change_flushes_cache(): void
    {
        $g = CustomerGroup::create([
            'name' => 'g'.uniqid(), 'display_name' => 'Г', 'markup_percentage' => 10,
            'discount_percentage' => 0, 'is_active' => true, 'sort_order' => 0,
        ]);

        ResponseCache::shouldReceive('clear')->atLeast()->once();
        ResponseCache::shouldReceive('forget')->zeroOrMoreTimes();

        $g->update(['markup_percentage' => 40]);
    }

    public function test_batch_editor_mass_price_update_flushes_cache(): void
    {
        if (! Schema::hasTable('batch_editor_logs')) {
            $this->markTestSkipped('batch_editor вимкнено в тестовій БД');
        }
        $p = Product::factory()->create(['is_active' => true, 'price' => 100]);

        ResponseCache::shouldReceive('clear')->atLeast()->once();
        ResponseCache::shouldReceive('forget')->zeroOrMoreTimes();

        app(\App\Services\BatchEditorService::class)->batchUpdatePrice([$p->id], 'set', 250);

        $this->assertSame(250.0, (float) $p->fresh()->price);
    }
}
