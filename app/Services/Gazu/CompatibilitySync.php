<?php

namespace App\Services\Gazu;

use App\Models\Product;
use Illuminate\Support\Facades\Schema;

/**
 * Синхронізує адмінський JSON `products.compatibility` (марка/модель/роки/двигун)
 * у pivot `product_compatibility` (relation compatibleEngines, engine_id), який
 * реально використовує фільтр підбору по авто (CatalogQuery::applyVehicle) та
 * віджет «Перевірити сумісність».
 *
 * Правила рядка:
 *   • конкретний двигун (label) і БЕЗ прапорця → лише цей двигун;
 *   • прапорець `all_engines=true` АБО двигун не вказано → ВСІ активні двигуни
 *     обраної моделі («Додати всі варіації»).
 *
 * Робастність: назви марки/моделі порівнюємо case-insensitive + без зайвих
 * пробілів; двигуни збираємо з УСІХ моделей, що збігаються (у БД бувають
 * дублікати CarModel після ре-сідінгу — інакше ->first() міг узяти дубль без
 * двигунів); label двигуна нормалізуємо (дефіс/тире/пробіли).
 */
class CompatibilitySync
{
    /** Нормалізація для порівняння: trim + сколапс пробілів + lower. */
    private static function norm(string $s): string
    {
        return mb_strtolower(trim(preg_replace('/\s+/u', ' ', $s)));
    }

    /** Нормалізація label двигуна: додатково зводимо всі тире/дефіси до '-'. */
    private static function normLabel(string $s): string
    {
        return self::norm(preg_replace('/[\x{2010}-\x{2015}\x{2212}]/u', '-', $s));
    }

    /**
     * @return int Кількість прив'язаних двигунів (для діагностики).
     */
    public static function syncProduct(Product $product): int
    {
        if (! Schema::hasTable('product_compatibility') || ! Schema::hasTable('car_engines')) {
            return 0;
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
            $makeName = self::norm((string) ($row['make'] ?? ''));
            $modelName = self::norm((string) ($row['model'] ?? ''));
            $engineLabel = trim((string) ($row['engine'] ?? ''));
            $allEngines = ! empty($row['all_engines']) || $engineLabel === '';

            if ($makeName === '' || $modelName === '') {
                continue;
            }

            // Усі моделі, що збігаються за назвою+маркою (з урахуванням дублів).
            $modelIds = \App\Models\CarModel::query()
                ->whereRaw('LOWER(TRIM(name)) = ?', [$modelName])
                ->whereHas('make', fn ($q) => $q->whereRaw('LOWER(TRIM(name)) = ?', [$makeName]))
                ->pluck('id')
                ->all();
            if (empty($modelIds)) {
                continue;
            }

            $engQ = \App\Models\CarEngine::query()
                ->whereIn('model_id', $modelIds)
                ->where('is_active', true);

            if (! $allEngines) {
                // Конкретний двигун — зіставляємо за нормалізованим label (або code).
                $wanted = self::normLabel($engineLabel);
                $match = $engQ->get(['id', 'label', 'code'])
                    ->first(fn ($e) => self::normLabel((string) $e->label) === $wanted
                        || self::normLabel((string) $e->code) === $wanted);
                if ($match) {
                    $engineIds[] = (int) $match->id;
                }
            } else {
                foreach ($engQ->pluck('id')->all() as $id) {
                    $engineIds[] = (int) $id;
                }
            }
        }

        $engineIds = array_values(array_unique(array_filter($engineIds)));
        $product->compatibleEngines()->sync($engineIds);

        return count($engineIds);
    }
}
