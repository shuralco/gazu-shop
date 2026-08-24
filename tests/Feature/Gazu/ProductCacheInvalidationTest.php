<?php

namespace Tests\Feature\Gazu;

use App\Models\Product;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Spatie\ResponseCache\Facades\ResponseCache;
use Tests\TestCase;

/**
 * Кеш вітрини мусить оновлюватись САМ, щойно товар змінили в адмінці.
 *
 * Регресія 24.08.2026: інвалідація була scoped — забувались лише «голі» URL
 * (/catalog, сторінка товару, категорії). Але ключ кешу містить query-string
 * (GazuCacheProfile::useCacheNameSuffix), тож ?page=2, фільтри, сортування й
 * пошук лишались зі старим HTML до кінця TTL: щойно завантажене фото не
 * зʼявлялось у картці товару.
 */
class ProductCacheInvalidationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_saving_product_clears_whole_response_cache(): void
    {
        ResponseCache::shouldReceive('clear')->atLeast()->once();

        Product::factory()->create(['is_active' => true, 'price' => 100]);
    }

    public function test_batch_editor_invalidates_cache_despite_bypassing_model_events(): void
    {
        $product = Product::factory()->create(['is_active' => true, 'price' => 100]);

        // Після створення товару чекаємо саме на інвалідацію від пакетної операції.
        ResponseCache::shouldReceive('clear')->atLeast()->once();

        app(\App\Services\BatchEditorService::class)
            ->batchUpdatePrice([$product->id], 'set', 250.0);

        // Ціна справді змінилась в обхід подій моделі.
        $this->assertEquals(250.0, (float) $product->fresh()->price);
    }
}
