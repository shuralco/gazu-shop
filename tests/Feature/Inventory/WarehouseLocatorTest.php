<?php

namespace Tests\Feature\Inventory;

use App\Models\MerchantWarehouse;
use App\Services\Geo\GeoLocator;
use App\Services\Warehouse\WarehouseLocator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Mockery;
use Tests\TestCase;

/**
 * Phase 6 — geo-detect closest warehouse.
 *
 * Locator picks closest active warehouse with coords by haversine, falls
 * back to city match → default → first active. Geo lookup itself is
 * mocked (no real HTTP to ip-api.com in tests).
 */
class WarehouseLocatorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // The seed_default_merchant_warehouse migration injects a MAIN-01 row.
        // Wipe it so each test owns its warehouse fixture without ID collisions.
        // Use the Schema helper so it stays driver-agnostic (the test runs on
        // sqlite :memory:, which chokes on MySQL's `SET FOREIGN_KEY_CHECKS`).
        Schema::disableForeignKeyConstraints();
        MerchantWarehouse::query()->delete();
        Schema::enableForeignKeyConstraints();
    }

    public function test_falls_back_to_default_when_no_geo(): void
    {
        $default = MerchantWarehouse::factory()->default()->create([
            'code' => 'MAIN', 'city' => 'Київ',
            'latitude' => 50.45, 'longitude' => 30.52,
        ]);
        MerchantWarehouse::factory()->create(['code' => 'LV', 'city' => 'Львів', 'is_default' => false]);

        $geo = Mockery::mock(GeoLocator::class);
        $geo->shouldReceive('locate')->andReturn(null);

        $locator = new WarehouseLocator($geo);
        $request = request()->create('/p', 'GET');
        $request->setLaravelSession(app('session.store'));

        $closest = $locator->closestForRequest($request);

        $this->assertSame($default->id, $closest->id);
    }

    public function test_haversine_returns_closest_by_coords(): void
    {
        $kyiv = MerchantWarehouse::factory()->create(['code' => 'KY', 'city' => 'Київ', 'latitude' => 50.45, 'longitude' => 30.52, 'is_default' => false]);
        $lviv = MerchantWarehouse::factory()->create(['code' => 'LV', 'city' => 'Львів', 'latitude' => 49.84, 'longitude' => 24.03, 'is_default' => false]);
        $kharkiv = MerchantWarehouse::factory()->create(['code' => 'KH', 'city' => 'Харків', 'latitude' => 49.99, 'longitude' => 36.23, 'is_default' => false]);

        // Mock GeoLocator → user is in Lviv
        $geo = Mockery::mock(GeoLocator::class);
        $geo->shouldReceive('locate')->andReturn([
            'ip' => '188.163.84.1', 'country' => 'Ukraine', 'country_code' => 'UA',
            'city' => 'Lviv', 'lat' => 49.8397, 'lng' => 24.0297,
        ]);

        $locator = new WarehouseLocator($geo);
        $request = request()->create('/p', 'GET', server: ['REMOTE_ADDR' => '188.163.84.1']);
        $request->setLaravelSession(app('session.store'));

        $closest = $locator->closestForRequest($request);

        $this->assertSame($lviv->id, $closest->id);
    }

    public function test_city_name_match_when_no_coords(): void
    {
        // Warehouse without coords but matching city
        $kyiv = MerchantWarehouse::factory()->create(['code' => 'KY', 'city' => 'Київ', 'latitude' => null, 'longitude' => null, 'is_default' => false]);
        MerchantWarehouse::factory()->default()->create(['code' => 'OTHER', 'city' => 'Інше', 'latitude' => null, 'longitude' => null]);

        $geo = Mockery::mock(GeoLocator::class);
        $geo->shouldReceive('locate')->andReturn([
            'ip' => '8.8.8.8', 'city' => 'Київ', 'lat' => null, 'lng' => null, 'country' => 'UA', 'country_code' => 'UA',
        ]);

        $locator = new WarehouseLocator($geo);
        $request = request()->create('/p', 'GET', server: ['REMOTE_ADDR' => '8.8.8.8']);
        $request->setLaravelSession(app('session.store'));

        $closest = $locator->closestForRequest($request);

        $this->assertSame($kyiv->id, $closest->id);
    }

    public function test_returns_null_when_no_active_warehouses(): void
    {
        $geo = Mockery::mock(GeoLocator::class);
        $geo->shouldReceive('locate')->andReturn(null);

        $locator = new WarehouseLocator($geo);
        $request = request()->create('/p', 'GET');
        $request->setLaravelSession(app('session.store'));

        $this->assertNull($locator->closestForRequest($request));
    }

    public function test_session_cache_short_circuits_repeat_lookups(): void
    {
        $kyiv = MerchantWarehouse::factory()->default()->create(['code' => 'KY']);

        $geo = Mockery::mock(GeoLocator::class);
        $geo->shouldReceive('locate')->once()->andReturn(null); // hit ONCE

        $locator = new WarehouseLocator($geo);
        $request = request()->create('/p', 'GET');
        $request->setLaravelSession(app('session.store'));

        $first = $locator->closestForRequest($request);
        $second = $locator->closestForRequest($request);

        $this->assertSame($first->id, $second->id);
    }
}
