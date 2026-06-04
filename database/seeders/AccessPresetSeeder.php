<?php

namespace Database\Seeders;

use App\Models\AccessPreset;
use App\Support\Access\AccessControl;
use Illuminate\Database\Seeder;

/**
 * Seeds sensible default access presets. Idempotent (updateOrCreate by key).
 * The permission matrix is generated from the live section registry, so new
 * resources/pages are covered automatically when re-seeded.
 */
class AccessPresetSeeder extends Seeder
{
    public function run(): void
    {
        $sections = AccessControl::sections();

        // grant: for sections matching $groups, give listed abilities (others false)
        $grant = function (array $groups, array $abilities) use ($sections): array {
            $map = [];
            foreach ($sections as $s) {
                if (! in_array($s['group'], $groups, true)) {
                    continue;
                }
                $map[$s['section']] = collect($s['abilities'])
                    ->mapWithKeys(fn ($a) => [$a => in_array($a, $abilities, true)])->all();
            }

            return $map;
        };

        $allGroups = collect($sections)->pluck('group')->unique()->values()->all();

        $presets = [
            ['admin_full', 'Адміністратор', 'Повний доступ до всіх розділів.', true, 0,
                $grant($allGroups, ['view', 'create', 'update', 'delete'])],
            ['orders_manager', 'Менеджер замовлень', 'Замовлення, платежі, клієнти; каталог — перегляд.', false, 10,
                array_replace($grant(['Продажі'], ['view', 'create', 'update']), $grant(['Каталог'], ['view']))],
            ['content_editor', 'Контент-менеджер', 'Контент і SEO повністю; каталог/аналітика — перегляд.', false, 20,
                array_replace($grant(['Контент і SEO'], ['view', 'create', 'update', 'delete']), $grant(['Каталог', 'Аналітика'], ['view']))],
            ['warehouse_operator', 'Склад і доставка', 'Склад, доставка, ТТН; каталог — перегляд.', false, 30,
                array_replace($grant(['Склад і доставка'], ['view', 'create', 'update']), $grant(['Каталог'], ['view']))],
        ];

        foreach ($presets as [$key, $name, $desc, $isSystem, $sort, $perms]) {
            AccessPreset::updateOrCreate(
                ['key' => $key],
                ['name' => $name, 'description' => $desc, 'is_system' => $isSystem, 'sort_order' => $sort, 'permissions' => $perms],
            );
        }
    }
}
