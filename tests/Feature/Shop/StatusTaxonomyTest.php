<?php

namespace Tests\Feature\Shop;

use App\Models\OrderStatus;
use App\Models\StockStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * Довідники-таксономії статусів: замовлень (OrderStatus) і наявності
 * товару (StockStatus). Обидва — DB-driven, кешовані, із seed-дефолтами.
 */
class StatusTaxonomyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // OrderStatus сіється міграцією — чистимо для детермінованих OrderStatus-перевірок
        // (StockStatus лишаємо засіяним: окремі тести перевіряють саме дефолти).
        OrderStatus::query()->delete();
        Cache::forget('order_statuses:map');
        Cache::forget('stock_statuses:map');
    }

    // ----------------------------------------------------------- OrderStatus

    public function test_order_status_options_active_only_sorted(): void
    {
        OrderStatus::create(['key' => 'new', 'label' => 'Новий', 'sort_order' => 1, 'is_active' => true]);
        OrderStatus::create(['key' => 'done', 'label' => 'Готово', 'sort_order' => 2, 'is_active' => true]);
        OrderStatus::create(['key' => 'hidden', 'label' => 'Прихований', 'sort_order' => 3, 'is_active' => false]);
        Cache::forget('order_statuses:map');

        $opts = OrderStatus::options();

        $this->assertSame(['new' => 'Новий', 'done' => 'Готово'], $opts);
    }

    public function test_order_status_default_key(): void
    {
        OrderStatus::create(['key' => 'new', 'label' => 'Новий', 'sort_order' => 1, 'is_default' => false, 'is_active' => true]);
        OrderStatus::create(['key' => 'pending', 'label' => 'Очікує', 'sort_order' => 2, 'is_default' => true, 'is_active' => true]);
        Cache::forget('order_statuses:map');

        $this->assertSame('pending', OrderStatus::defaultKey());
    }

    public function test_order_status_colors_map(): void
    {
        OrderStatus::create(['key' => 'done', 'label' => 'Готово', 'color' => 'success', 'is_active' => true]);
        Cache::forget('order_statuses:map');

        $this->assertSame(['done' => 'success'], OrderStatus::colors());
    }

    // ----------------------------------------------------------- StockStatus

    public function test_stock_status_seeded_defaults_present(): void
    {
        // міграція core сіє 4 дефолтні статуси
        $this->assertSame(4, StockStatus::count());
        $this->assertArrayHasKey('in_stock', StockStatus::options());
        $this->assertArrayHasKey('preorder', StockStatus::options());
    }

    public function test_stock_status_default_is_in_stock(): void
    {
        $this->assertSame('in_stock', StockStatus::defaultKey());
    }

    public function test_stock_status_availability_schema_map(): void
    {
        $avail = StockStatus::availabilities();
        $this->assertSame('InStock', $avail['in_stock']);
        $this->assertSame('PreOrder', $avail['preorder']);
        $this->assertSame('OutOfStock', $avail['out_of_stock']);
    }

    public function test_stock_status_orderable_flag(): void
    {
        $orderable = StockStatus::orderable();
        $this->assertTrue($orderable['in_stock']);
        $this->assertFalse($orderable['out_of_stock']);
    }

    public function test_stock_status_by_key_returns_record(): void
    {
        $st = StockStatus::byKey('preorder');
        $this->assertNotNull($st);
        $this->assertSame('Передзамовлення', $st->label);
        $this->assertNull(StockStatus::byKey('nonexistent'));
    }

    public function test_stock_status_inactive_excluded(): void
    {
        StockStatus::where('key', 'preorder')->update(['is_active' => false]);
        Cache::forget('stock_statuses:map');

        $this->assertArrayNotHasKey('preorder', StockStatus::options());
    }
}
