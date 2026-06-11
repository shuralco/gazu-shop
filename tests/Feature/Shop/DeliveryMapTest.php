<?php

namespace Tests\Feature\Shop;

use App\Filament\Widgets\DeliveryMapWidget;
use App\Models\Order;
use App\Support\Geo\UaCities;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DeliveryMapTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------- UaCities

    public function test_coords_for_known_cities(): void
    {
        $this->assertSame([50.4501, 30.5234], UaCities::coordsFor('Київ'));
        $this->assertSame([49.8397, 24.0297], UaCities::coordsFor('Львів'));
    }

    public function test_coords_strip_settlement_prefix(): void
    {
        $this->assertSame(UaCities::coordsFor('Одеса'), UaCities::coordsFor('м. Одеса'));
        $this->assertSame(UaCities::coordsFor('Київ'), UaCities::coordsFor('місто Київ'));
    }

    public function test_coords_unknown_city_null(): void
    {
        $this->assertNull(UaCities::coordsFor('смт Невідомеселище'));
        $this->assertNull(UaCities::coordsFor(null));
        $this->assertNull(UaCities::coordsFor(''));
    }

    // ----------------------------------------------------------- DeliveryMap

    public function test_empty_when_no_orders(): void
    {
        $data = (new DeliveryMapWidget)->getMapData();

        $this->assertSame(0, $data['total']);
        $this->assertSame([], $data['points']);
    }

    public function test_aggregates_orders_by_city_with_coords(): void
    {
        foreach (['Київ', 'Київ', 'Київ', 'Львів', 'смт Невідоме'] as $city) {
            Order::create(['email' => uniqid().'@t.test', 'shipping_city' => $city, 'total' => 100]);
        }

        $data = (new DeliveryMapWidget)->getMapData();

        $this->assertSame(5, $data['total']);
        $this->assertSame(4, $data['mapped']);   // 3 Київ + 1 Львів
        $this->assertSame(1, $data['unknown']);  // смт Невідоме

        // точки відсортовані за кількістю спадно → Київ перший із count=3
        $this->assertSame('Київ', $data['points'][0]['city']);
        $this->assertSame(3, $data['points'][0]['count']);
        $this->assertEqualsWithDelta(50.4501, $data['points'][0]['lat'], 0.001);
    }

    public function test_ignores_empty_shipping_city(): void
    {
        Order::create(['email' => 'a@t.test', 'shipping_city' => '', 'total' => 100]);
        Order::create(['email' => 'b@t.test', 'shipping_city' => null, 'total' => 100]);

        $this->assertSame(0, (new DeliveryMapWidget)->getMapData()['total']);
    }
}
