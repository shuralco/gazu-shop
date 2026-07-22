<?php

namespace Tests\Feature;

use App\Models\AccessPreset;
use App\Support\Access\AccessControl;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

/**
 * Контракт пресету «Адмін клієнта» (client_admin): повне керування магазином,
 * без службових/dev-розділів. Ловить прогалини на кшталт «забули групу
 * Оплата і доставка» — саме через це клієнт не бачив Способи оплати/доставки.
 */
class ClientAdminPresetTest extends TestCase
{
    use LazilyRefreshDatabase;

    /** @return array<string,bool> section => can view */
    private function viewMap(): array
    {
        $this->seed(\Database\Seeders\AccessPresetSeeder::class);
        $perms = AccessPreset::where('key', 'client_admin')->firstOrFail()->permissions ?? [];

        $map = [];
        foreach (AccessControl::sections() as $s) {
            $map[$s['group'].' :: '.$s['section']] = (bool) ($perms[$s['section']]['view'] ?? false);
        }

        return $map;
    }

    public function test_client_admin_sees_every_business_group(): void
    {
        $businessGroups = [
            'Каталог', 'Продажі', 'Склад і доставка',
            'Оплата і доставка', 'Контент і SEO', 'Аналітика', 'Налаштування',
        ];
        $map = $this->viewMap();

        foreach ($businessGroups as $group) {
            $inGroup = array_filter($map, fn ($k) => str_starts_with($k, $group.' :: '), ARRAY_FILTER_USE_KEY);
            $this->assertNotEmpty($inGroup, "група «{$group}» має існувати в реєстрі розділів");

            // DemoCatalogGenerator свідомо виключений навіть із дозволених груп.
            $hidden = array_keys(array_filter(
                $inGroup,
                fn ($view, $k) => ! $view && ! str_contains($k, 'DemoCatalogGenerator'),
                ARRAY_FILTER_USE_BOTH
            ));

            $this->assertSame([], $hidden, "клієнт-адмін має бачити всі розділи групи «{$group}»");
        }
    }

    public function test_client_admin_does_not_see_service_groups(): void
    {
        $map = $this->viewMap();

        foreach (['Обслуговування', 'Система'] as $group) {
            $inGroup = array_filter($map, fn ($k) => str_starts_with($k, $group.' :: '), ARRAY_FILTER_USE_KEY);
            $this->assertNotEmpty($inGroup, "група «{$group}» має існувати");

            $visible = array_keys(array_filter($inGroup));
            $this->assertSame([], $visible, "клієнт-адмін НЕ має бачити службову групу «{$group}»");
        }
    }

    public function test_payment_and_delivery_sections_are_visible(): void
    {
        // Регресія: саме ці розділи (Способи оплати/доставки, Кошик) клієнт не бачив.
        $map = $this->viewMap();
        $payDelivery = array_filter($map, fn ($k) => str_starts_with($k, 'Оплата і доставка :: '), ARRAY_FILTER_USE_KEY);

        $this->assertNotEmpty($payDelivery, 'група «Оплата і доставка» має існувати');

        $hidden = array_keys(array_filter($payDelivery, fn ($view) => ! $view));
        $this->assertSame([], $hidden, 'усі розділи оплати/доставки мають бути видимі клієнт-адміну');
    }
}
