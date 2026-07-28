<?php

namespace Tests\Feature;

use App\Models\CustomerGroup;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Modules\PricingMarkup\Services\MarkupPricing;
use Tests\TestCase;

/**
 * Модуль pricing_markup: ціна = базова + % націнки групи клієнта.
 * Гість / клієнт без групи → «стандартна» група (is_default).
 */
class PricingMarkupTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        MarkupPricing::flush();
        CustomerGroup::query()->delete();
    }

    private function group(string $name, float $markup, bool $default = false, bool $active = true): CustomerGroup
    {
        return CustomerGroup::create([
            'name' => \Str::slug($name),
            'display_name' => $name,
            'markup_percentage' => $markup,
            'discount_percentage' => 0,
            'is_default' => $default,
            'is_active' => $active,
            'sort_order' => 0,
        ]);
    }

    private function product(float $base = 1000): Product
    {
        return Product::factory()->create([
            'is_active' => true,
            'price' => $base,
            'price_currency' => 'UAH',
        ]);
    }

    private function user(?CustomerGroup $group): User
    {
        return User::factory()->create(['customer_group_id' => $group?->id]);
    }

    public function test_guest_gets_default_group_markup(): void
    {
        $this->group('Роздріб', 35, default: true);
        $p = $this->product(1000);

        $pv = $p->priceViewForUser(null);

        $this->assertSame(1350.0, $pv['price'], 'гість → стандартна група +35 %');
        $this->assertSame(1350.0, $pv['regular'], 'націнена ціна стає «звичайною»');
        $this->assertFalse($pv['is_group'], 'націнка не є знижкою — перекресленої ціни бути не має');
    }

    public function test_user_without_group_falls_back_to_default(): void
    {
        $this->group('Роздріб', 35, default: true);
        $p = $this->product(1000);

        $this->assertSame(1350.0, $p->priceViewForUser($this->user(null))['price']);
    }

    public function test_user_group_markup_wins_over_default(): void
    {
        $this->group('Роздріб', 35, default: true);
        $wholesale = $this->group('Гурт', 10);
        $p = $this->product(1000);

        $this->assertSame(1100.0, $p->priceViewForUser($this->user($wholesale))['price'], 'своя група +10 %');
    }

    public function test_inactive_user_group_falls_back_to_default(): void
    {
        $this->group('Роздріб', 35, default: true);
        $off = $this->group('Вимкнена', 5, active: false);
        $p = $this->product(1000);

        $this->assertSame(1350.0, $p->priceViewForUser($this->user($off))['price'], 'вимкнена група → стандартна');
    }

    public function test_zero_markup_keeps_base_price(): void
    {
        $this->group('Без націнки', 0, default: true);
        $p = $this->product(1000);

        $this->assertSame(1000.0, $p->priceViewForUser(null)['price']);
    }

    public function test_negative_markup_is_cheaper_than_base(): void
    {
        $this->group('Роздріб', 0, default: true);
        $vip = $this->group('VIP', -10);
        $p = $this->product(1000);

        $this->assertSame(900.0, $p->priceViewForUser($this->user($vip))['price']);
    }

    public function test_no_default_group_keeps_base_price(): void
    {
        $this->group('Гурт', 50); // жодної стандартної
        $p = $this->product(1000);

        $this->assertSame(1000.0, $p->priceViewForUser(null)['price'], 'без стандартної групи гість платить базову');
    }

    public function test_fractional_markup_rounds_to_kopecks(): void
    {
        $this->group('Роздріб', 33.33, default: true);
        $p = $this->product(199.99);

        // 199.99 * 1.3333 = 266.6533...
        $this->assertSame(266.65, $p->priceViewForUser(null)['price']);
    }

    public function test_only_one_group_stays_default(): void
    {
        $first = $this->group('Перша', 10, default: true);
        $second = $this->group('Друга', 20, default: true);

        $this->assertFalse($first->fresh()->is_default, 'позначка знімається з попередньої');
        $this->assertTrue($second->fresh()->is_default);
        $this->assertSame(1, CustomerGroup::where('is_default', true)->count());
    }

    public function test_markup_applies_to_warehouse_price_too(): void
    {
        $this->group('Роздріб', 20, default: true);
        $p = $this->product(1000);

        // Ціна складу передається як base — націнка мусить діяти й на неї.
        $this->assertSame(600.0, $p->priceViewForUser(null, 1, 500.0)['price']);
    }

    public function test_group_discount_applies_on_top_of_marked_up_price(): void
    {
        // Націнка 50 % + знижка 10 % → 1000 → 1500 → 1350, і це саме знижка
        // (є перекреслена ціна 1500).
        $group = $this->group('Акційна', 50, default: true);
        $group->update(['discount_percentage' => 10]);
        MarkupPricing::flush();

        $p = $this->product(1000);
        $pv = $p->priceViewForUser($this->user($group));

        $this->assertSame(1500.0, $pv['regular'], 'звичайна = базова + націнка');
        $this->assertSame(1350.0, $pv['price'], 'знижка 10 % від націненої');
        $this->assertTrue($pv['is_group'], 'тут знижка є — перекреслена ціна доречна');
    }

    public function test_default_group_resolution_is_exposed(): void
    {
        $retail = $this->group('Роздріб', 35, default: true);

        $this->assertSame($retail->id, MarkupPricing::defaultGroup()?->id);
        $this->assertSame(35.0, MarkupPricing::percentFor(null));
        $this->assertSame(1350.0, MarkupPricing::apply(1000, null));
    }

    public function test_discount_group_converts_to_equivalent_negative_markup(): void
    {
        // Перехід опту зі знижки на націнку: -X % націнки дає ТУ САМУ ціну,
        // що й X % знижки. Різниця лише в подачі — зникає перекреслена ціна.
        $this->group('Роздріб', 0, default: true);
        $wholesale = $this->group('Опт', 0);
        $wholesale->update(['discount_percentage' => 17.5]);
        MarkupPricing::flush();

        $p = $this->product(499);
        $user = $this->user($wholesale);

        $before = $p->priceViewForUser($user);
        $this->assertSame(411.68, $before['price'], '499 - 17.5% = 411.68');
        $this->assertSame(499.0, $before['regular']);
        $this->assertTrue($before['is_group'], 'зі знижкою є перекреслена ціна');

        // Конвертація: знижку в нуль, націнку у мінус на ту саму величину.
        $wholesale->update(['discount_percentage' => 0, 'markup_percentage' => -17.5]);
        MarkupPricing::flush();

        $after = $p->fresh()->priceViewForUser($user->fresh());
        $this->assertSame($before['price'], $after['price'], 'ціна для клієнта не змінюється');
        $this->assertSame(411.68, $after['regular'], 'націнена ціна стає «звичайною»');
        $this->assertFalse($after['is_group'], 'перекресленої ціни більше немає — це не знижка');
    }

    public function test_conversion_does_not_touch_other_groups(): void
    {
        $retail = $this->group('Роздріб', 0, default: true);
        $wholesale = $this->group('Опт', -17.5);
        $p = $this->product(1000);

        $this->assertSame(1000.0, $p->priceViewForUser(null)['price'], 'гість платить базову');
        $this->assertSame(1000.0, $p->priceViewForUser($this->user($retail))['price'], 'роздріб без змін');
        $this->assertSame(825.0, $p->priceViewForUser($this->user($wholesale))['price'], 'опт -17.5%');
    }
}
