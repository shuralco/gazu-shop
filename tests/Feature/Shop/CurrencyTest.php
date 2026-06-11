<?php

namespace Tests\Feature\Shop;

use App\Models\Currency;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CurrencyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // міграція сіє дефолтні валюти — чистимо для детермінованих перевірок
        Currency::query()->delete();
        Cache::forget('currencies:map');
    }

    public function test_available_map_null_when_no_rows(): void
    {
        $this->assertNull(Currency::availableMap());
    }

    public function test_available_map_lists_active_only(): void
    {
        Currency::create(['code' => 'UAH', 'name' => 'Гривня', 'symbol' => '₴', 'rate' => 1, 'is_base' => true, 'is_active' => true, 'sort_order' => 1]);
        Currency::create(['code' => 'USD', 'name' => 'Долар', 'symbol' => '$', 'rate' => 0.025, 'is_active' => true, 'sort_order' => 2]);
        Currency::create(['code' => 'EUR', 'name' => 'Євро', 'symbol' => '€', 'rate' => 0.023, 'is_active' => false, 'sort_order' => 3]);
        Cache::forget('currencies:map');

        $map = Currency::availableMap();

        $this->assertArrayHasKey('UAH', $map);
        $this->assertArrayHasKey('USD', $map);
        $this->assertArrayNotHasKey('EUR', $map);
        $this->assertSame(0.025, $map['USD']['rate']);
    }

    public function test_base_code_returns_flagged_base(): void
    {
        Currency::create(['code' => 'UAH', 'name' => 'Гривня', 'symbol' => '₴', 'rate' => 1, 'is_base' => true, 'is_active' => true, 'sort_order' => 1]);
        Currency::create(['code' => 'USD', 'name' => 'Долар', 'symbol' => '$', 'rate' => 0.025, 'is_active' => true, 'sort_order' => 2]);
        Cache::forget('currencies:map');

        $this->assertSame('UAH', Currency::baseCode());
    }

    public function test_cache_flushed_on_save(): void
    {
        Currency::create(['code' => 'UAH', 'name' => 'Гривня', 'symbol' => '₴', 'rate' => 1, 'is_base' => true, 'is_active' => true]);
        $this->assertArrayHasKey('UAH', Currency::availableMap()); // primes cache

        Currency::create(['code' => 'USD', 'name' => 'Долар', 'symbol' => '$', 'rate' => 0.025, 'is_active' => true]);

        // observer мав скинути кеш → USD одразу видно
        $this->assertArrayHasKey('USD', Currency::availableMap());
    }
}
