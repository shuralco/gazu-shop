<?php

namespace Tests\Feature;

use App\Filament\Pages\PricingMarkupSettings;
use App\Models\CustomerGroup;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Livewire\Livewire;
use Modules\PricingMarkup\Services\MarkupPricing;
use Tests\TestCase;

/**
 * Інтерфейс керування націнками (Продажі → «Націнки по групах»):
 * завантаження груп, калькулятор-прев'ю, збереження, захист від помилок.
 */
class PricingMarkupSettingsPageTest extends TestCase
{
    use LazilyRefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        MarkupPricing::flush();
        CustomerGroup::query()->delete();
        // Програмна авторизація — без введення пароля.
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    private function group(string $name, float $markup, bool $default = false): CustomerGroup
    {
        return CustomerGroup::create([
            'name' => \Str::slug($name), 'display_name' => $name,
            'markup_percentage' => $markup, 'discount_percentage' => 0,
            'is_default' => $default, 'is_active' => true, 'sort_order' => 0,
        ]);
    }

    public function test_page_renders_with_existing_groups(): void
    {
        $this->group('Роздріб', 35, default: true);
        $this->group('Гурт', 10);

        Livewire::test(PricingMarkupSettings::class)
            ->assertOk()
            ->assertSee('Роздріб')
            ->assertSee('Гурт');
    }

    public function test_preview_calculator_uses_the_same_formula(): void
    {
        $this->group('Роздріб', 35, default: true);
        $this->group('Гурт', 10);

        $rows = Livewire::test(PricingMarkupSettings::class)
            ->set('sample', 1000)
            ->instance()
            ->previewRows();

        $prices = collect($rows)->pluck('price', 'name');
        $this->assertSame(1350.0, $prices['Роздріб'], 'калькулятор: 1000 +35 %');
        $this->assertSame(1100.0, $prices['Гурт'], 'калькулятор: 1000 +10 %');
    }

    public function test_saving_updates_markup_and_default_group(): void
    {
        $retail = $this->group('Роздріб', 35, default: true);
        $wholesale = $this->group('Гурт', 10);

        $page = Livewire::test(PricingMarkupSettings::class);
        // Repeater адресує рядки UUID-ключами, не індексами.
        [$firstKey, $secondKey] = array_keys($page->get('data')['groups']);

        $page->set("data.groups.{$firstKey}.markup_percentage", 40)
            ->set("data.groups.{$firstKey}.is_default", false)
            ->set("data.groups.{$secondKey}.is_default", true)
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame(40.0, (float) $retail->fresh()->markup_percentage);
        $this->assertTrue($wholesale->fresh()->is_default, 'стандартною стала друга група');
        $this->assertFalse($retail->fresh()->is_default, 'позначка знялася з першої');
        $this->assertSame(1, CustomerGroup::where('is_default', true)->count());
    }

    public function test_new_group_can_be_added_from_the_page(): void
    {
        $retail = $this->group('Роздріб', 35, default: true);

        $page = Livewire::test(PricingMarkupSettings::class);
        $existing = $page->get('data')['groups'];

        // Новий рядок = ще один елемент стану Repeater (як після «Додати групу»).
        $existing['new-dealer'] = [
            'id' => null,
            'display_name' => 'Дилер',
            'markup_percentage' => 5,
            'is_default' => false,
            'is_active' => true,
        ];

        $page->set('data.groups', $existing)
            ->call('save')
            ->assertHasNoErrors();

        $dealer = CustomerGroup::where('display_name', 'Дилер')->first();
        $this->assertNotNull($dealer, 'нова група створюється');
        $this->assertSame(5.0, (float) $dealer->markup_percentage);
        $this->assertNotEmpty($dealer->name, 'технічний ключ заповнюється автоматично');
        $this->assertNotNull($retail->fresh(), 'наявна група лишається');
    }

    public function test_group_with_customers_is_not_deleted(): void
    {
        $retail = $this->group('Роздріб', 35, default: true);
        $wholesale = $this->group('Гурт', 10);
        User::factory()->create(['customer_group_id' => $wholesale->id]);

        // Прибираємо другий рядок зі стану — сторінка мусить відмовитись видаляти.
        Livewire::test(PricingMarkupSettings::class)
            ->set('data.groups', [[
                'id' => $retail->id,
                'display_name' => 'Роздріб',
                'markup_percentage' => 35,
                'is_default' => true,
                'is_active' => true,
            ]])
            ->call('save');

        $this->assertNotNull($wholesale->fresh(), 'групу з клієнтами видаляти не можна');
    }

    public function test_empty_group_list_is_rejected(): void
    {
        $retail = $this->group('Роздріб', 35, default: true);

        Livewire::test(PricingMarkupSettings::class)
            ->set('data.groups', [])
            ->call('save');

        $this->assertNotNull($retail->fresh(), 'останню групу не видаляємо');
    }

    public function test_default_is_auto_picked_when_none_marked(): void
    {
        $this->group('Роздріб', 35, default: true);

        $page = Livewire::test(PricingMarkupSettings::class);
        $key = array_key_first($page->get('data')['groups']);
        $page->set("data.groups.{$key}.is_default", false)->call('save');

        $this->assertSame(1, CustomerGroup::where('is_default', true)->count(), 'стандартна група мусить існувати завжди');
    }
}
