<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerGroupResource;
use App\Support\Hooks;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Модуль має бути СТАВНИМ у будь-який магазин на движку: поля адмінки приходять
 * із самого модуля через точки розширення wholesale, а не з правок його файлів.
 */
class PricingMarkupInstallableTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_module_registers_its_own_admin_fields(): void
    {
        $events = Hooks::eventsBySource('pricing_markup');

        $this->assertContains('wholesale.customer_group.form', $events);
        $this->assertContains('wholesale.customer_group.columns', $events);
        $this->assertContains('pricing.base_price', $events, 'ядро цінності — фільтр ціни');
    }

    public function test_markup_field_reaches_the_customer_group_form(): void
    {
        $names = collect(Hooks::filter('wholesale.customer_group.form', []))
            ->flatMap(fn ($c) => method_exists($c, 'getChildComponents') ? $c->getChildComponents() : [$c])
            ->map(fn ($c) => method_exists($c, 'getName') ? $c->getName() : null)
            ->filter()->all();

        $this->assertContains('markup_percentage', $names, 'поле націнки додається модулем');
        $this->assertContains('markup_preview', $names, 'приклад ціни поруч із полем');
    }

    public function test_markup_column_reaches_the_table(): void
    {
        $names = collect(Hooks::filter('wholesale.customer_group.columns', []))
            ->map(fn ($c) => method_exists($c, 'getName') ? $c->getName() : null)
            ->filter()->all();

        $this->assertContains('markup_percentage', $names);
    }

    public function test_wholesale_resource_does_not_hardcode_markup(): void
    {
        // Якщо поле знову впишуть у сам wholesale — модуль стане не встановлюваним.
        $source = file_get_contents(base_path('modules/wholesale/src/Filament/Resources/CustomerGroupResource.php'));

        $this->assertStringNotContainsString("make('markup_percentage')", $source,
            'поля націнки мають жити в модулі pricing_markup, а не у wholesale');
        $this->assertStringContainsString("wholesale.customer_group.form", $source,
            'у wholesale має лишатись точка розширення');
    }

    public function test_module_manifest_declares_dependency(): void
    {
        $manifest = json_decode(file_get_contents(base_path('modules/pricing_markup/module.json')), true);

        $this->assertSame('pricing_markup', $manifest['name']);
        $this->assertContains('wholesale', $manifest['requires_modules'], 'модуль спирається на групи клієнтів');
        $this->assertNotEmpty($manifest['providers']);
        $this->assertSame('database/migrations', $manifest['migrations_path']);
    }
}
