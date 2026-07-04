<?php

namespace App\Services\Gazu;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;

/**
 * Синхронізує адмінський JSON `products.compatibility` (марка/модель/роки/двигун)
 * у pivot `product_compatibility` (relation compatibleEngines, engine_id), який
 * реально використовує фільтр підбору по авто на сайті (CatalogQuery::applyVehicle)
 * та віджет «Перевірити сумісність».
 *
 * Правила рядка:
 *   • конкретний двигун (label) і БЕЗ прапорця → лише цей двигун;
 *   • прапорець `all_engines=true` АБО двигун не вказано → ВСІ активні двигуни
 *     обраної моделі («Додати всі варіації»).
 *
 * До цього редактор сумісності писав лише JSON і НЕ впливав на фільтр — тепер
 * впливає. Викликається з Product::saved (при зміні compatibility) + бекфіл
 * командою gazu:sync-compatibility.
 */
class CompatibilitySync
{
    public static function syncProduct(Product $product): void
    {
        if (! Schema::hasTable('product_compatibility') || ! Schema::hasTable('car_engines')) {
            return;
        }

        $rows = $product->compatibility;
        if (! is_array($rows)) {
            $rows = [];
        }

        $engineIds = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $makeName = trim((string) ($row['make'] ?? ''));
            $modelName = trim((string) ($row['model'] ?? ''));
            $engineLabel = trim((string) ($row['engine'] ?? ''));
            // «Усі варіації» — явний прапорець АБО двигун не вказано (рядок
            // рівня моделі логічно означає «підходить для всіх двигунів»).
            $allEngines = ! empty($row['all_engines']) || $engineLabel === '';

            if ($makeName === '' || $modelName === '') {
                continue;
            }

            $model = \App\Models\CarModel::query()
                ->where('name', $modelName)
                ->whereHas('make', fn ($q) => $q->where('name', $makeName))
                ->first();
            if (! $model) {
                continue;
            }

            if (! $allEngines) {
                $eng = \App\Models\CarEngine::query()
                    ->where('model_id', $model->id)
                    ->where('label', $engineLabel)
                    ->where('is_active', true)
                    ->value('id');
                if ($eng) {
                    $engineIds[] = (int) $eng;
                }
            } else {
                $ids = \App\Models\CarEngine::query()
                    ->where('model_id', $model->id)
                    ->where('is_active', true)
                    ->pluck('id')
                    ->all();
                foreach ($ids as $id) {
                    $engineIds[] = (int) $id;
                }
            }
        }

        $engineIds = array_values(array_unique(array_filter($engineIds)));
        $product->compatibleEngines()->sync($engineIds);
    }
}
