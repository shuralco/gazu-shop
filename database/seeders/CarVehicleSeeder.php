<?php

namespace Database\Seeders;

use App\Models\CarEngine;
use App\Models\CarMake;
use App\Models\CarModel;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * РЕАЛЬНИЙ каталог авто клієнта (Google Drive «Каталог автівок», власник
 * maxkurkowork@gmail.com). Замінює попередню демо-базу (ICE BYD F3, Chery
 * Tiggo тощо) на актуальний EV/PHEV-модельний ряд: BYD, Volkswagen (ID-серія),
 * Audi (e-tron).
 *
 * Колонки джерела: Бренд · Модель · Роки випуску · Модифікація.
 * Модифікація (варіант батареї/комплектації) маппиться на car_engines:
 *   label = повний текст модифікації, fuel_type = electric | hybrid (DM-i/PHEV),
 *   code = унікальний slug, years_range = роки рядка.
 * displacement/hp = null (електро — без обʼєму/паспортних к.с. у джерелі).
 *
 * РУЙНІВНИЙ: повністю очищує car_makes (FK-каскад → car_models → car_engines →
 * product_compatibility), потім засіває реальні дані. Запускати свідомо:
 *   php artisan db:seed --class=CarVehicleSeeder --force
 */
class CarVehicleSeeder extends Seeder
{
    /** Бренд → [slug, відображувана назва, лого]. */
    private array $makeMeta = [
        'BYD'  => ['byd', 'BYD', '/img/car-makes/byd.svg'],
        'VW'   => ['vw', 'Volkswagen', '/img/car-makes/vw.svg'],
        'Audi' => ['audi', 'Audi', '/img/car-makes/audi.svg'],
    ];

    /** Назва моделі → тип кузова (факт, для бейджа в селекторі). */
    private array $bodyByModel = [
        'Song Plus EV' => 'suv', 'Song L EV' => 'suv', 'Song L DM-i' => 'suv',
        'Sealion 07 EV' => 'suv', 'Sealion 07 DM-i' => 'suv',
        'Sealion 06 EV' => 'suv', 'Sealion 06 DM-i' => 'suv',
        'Sealion 05 EV' => 'suv', 'Sealion 05 DM-i' => 'suv',
        'Seagull' => 'hatchback', 'Dolphin' => 'hatchback',
        'Yuan UP' => 'crossover', 'Yuan Plus' => 'crossover',
        'ID.3' => 'hatchback', 'ID.4' => 'suv', 'ID.4 X' => 'suv', 'ID.4 Crozz' => 'suv',
        'ID.5' => 'crossover', 'ID.6 X' => 'suv', 'ID.6 Crozz' => 'suv', 'ID. UNYX 06' => 'suv',
        'ID.7' => 'sedan', 'ID.7 Tourer' => 'wagon', 'ID.7 Vizzion' => 'sedan',
        'Q4 e-tron' => 'suv', 'Q4 Sportback e-tron' => 'suv', 'Q5 e-tron' => 'suv',
    ];

    public function run(): void
    {
        // Очистити демо-базу. car_models/car_engines/product_compatibility
        // видаляться FK-каскадом (constrained()->cascadeOnDelete()).
        DB::table('car_makes')->delete();

        $rows = $this->rows();

        // Згрупувати за маркою → моделлю, зберігаючи порядок появи.
        $byMakeModel = [];
        foreach ($rows as [$brand, $model, $years, $mod]) {
            $byMakeModel[$brand][$model][] = ['years' => $years, 'mod' => trim($mod)];
        }

        $makeSort = 0;
        foreach ($byMakeModel as $brand => $models) {
            [$makeSlug, $makeName, $logo] = $this->makeMeta[$brand]
                ?? [Str::slug($brand), $brand, null];

            $make = CarMake::create([
                'slug' => $makeSlug,
                'name' => $makeName,
                'logo_path' => $logo,
                'sort_order' => $makeSort++,
                'is_active' => true,
            ]);

            $modelSort = 0;
            foreach ($models as $modelName => $variants) {
                $allYears = array_map(fn ($v) => $v['years'], $variants);

                $model = CarModel::create([
                    'make_id' => $make->id,
                    'slug' => $this->modelSlug($modelName),
                    'name' => $modelName,
                    'body_type' => $this->bodyByModel[$modelName] ?? null,
                    'years_range' => $this->mergeYears($allYears),
                    'sort_order' => $modelSort++,
                    'is_active' => true,
                ]);

                $engineSort = 0;
                $usedCodes = [];
                foreach ($variants as $v) {
                    CarEngine::create([
                        'model_id' => $model->id,
                        'code' => $this->engineCode($v['mod'], $usedCodes),
                        'label' => $v['mod'],
                        'fuel_type' => $this->fuelType($v['mod']),
                        'displacement' => null,
                        'hp' => null,
                        'years_range' => $this->normYears($v['years']),
                        'sort_order' => $engineSort++,
                        'is_active' => true,
                    ]);
                }
            }
        }
    }

    private function modelSlug(string $name): string
    {
        // "ID.4 X" → "id-4-x", "ID. UNYX 06" → "id-unyx-06", "Q4 e-tron" → "q4-e-tron"
        return Str::slug(str_replace('.', ' ', $name)) ?: Str::slug($name);
    }

    /** Унікальний (per-model) код двигуна зі slug модифікації, max 40 символів. */
    private function engineCode(string $mod, array &$used): string
    {
        $base = substr(Str::slug(str_replace('.', ' ', $mod)) ?: 'var', 0, 40);
        $code = $base;
        $i = 2;
        while (isset($used[$code])) {
            $code = substr($base, 0, 38).'-'.$i;
            $i++;
        }
        $used[$code] = true;

        return $code;
    }

    private function fuelType(string $mod): string
    {
        return (stripos($mod, 'PHEV') !== false || stripos($mod, 'DM-i') !== false)
            ? 'hybrid'
            : 'electric';
    }

    /** "2020–2023" → "2020-2023", "2024–" → "2024-". */
    private function normYears(string $y): string
    {
        return trim(str_replace(['–', '—'], '-', $y));
    }

    /** Обʼєднати роки всіх модифікацій моделі в один діапазон. */
    private function mergeYears(array $years): ?string
    {
        $starts = [];
        $ends = [];
        $open = false;
        foreach ($years as $y) {
            $y = $this->normYears($y);
            if (preg_match('/(\d{4})/', $y, $m)) {
                $starts[] = (int) $m[1];
            }
            if (preg_match('/-\s*(\d{4})/', $y, $m)) {
                $ends[] = (int) $m[1];
            } elseif (str_ends_with($y, '-')) {
                $open = true;
            }
        }
        if (! $starts) {
            return null;
        }
        $start = min($starts);

        if ($open || ! $ends) {
            return $start.'-';
        }

        return $start.'-'.max($ends);
    }

    /** @return array<int, array{0:string,1:string,2:string,3:string}> [brand, model, years, modification] */
    private function rows(): array
    {
        return [
            ['BYD', 'Song Plus EV', '2021-2023', '71.7 kWh'],
            ['BYD', 'Song Plus EV', '2023-', '71.8 kWh Champion Edition'],
            ['BYD', 'Song Plus EV', '2023-', '87 kWh Champion Edition'],
            ['BYD', 'Song Plus EV', '2024-', '71.8 kWh Honor Edition'],
            ['BYD', 'Song Plus EV', '2024-', '87 kWh Honor Edition'],
            ['BYD', 'Song Plus EV', '2025-', '71.8 kWh Smart Drive Edition'],
            ['BYD', 'Song Plus EV', '2025-', '87 kWh Smart Drive Edition'],

            ['VW', 'ID.4 X', '2021-', '45–82 kWh Pure / Pro'],
            ['VW', 'ID.4 X', '2021-', '82 kWh Prime AWD'],
            ['VW', 'ID.4 Crozz', '2021-', '45–82 kWh Pure / Pro'],
            ['VW', 'ID.4 Crozz', '2021-', '82 kWh Prime AWD'],
            ['VW', 'ID.4', '2020–2023', '55–62 kWh Pure (EU)'],
            ['VW', 'ID.4', '2020–2023', '82 kWh Pro (EU/USA)'],
            ['VW', 'ID.4', '2021–2023', '82 kWh GTX / AWD 4MOTION (EU/USA)'],
            ['VW', 'ID.4', '2024–', '52–62 kWh Pure (EU/USA)'],
            ['VW', 'ID.4', '2024–', '82 kWh Pro / Pro S (EU/USA)'],
            ['VW', 'ID.4', '2024–', '82 kWh GTX / AWD Pro S (EU/USA)'],
            ['VW', 'ID.3', '2019–2023', '45 kWh Pure / Pure Performance (EU)'],
            ['VW', 'ID.3', '2019–2023', '58 kWh Pro / Pro Performance (EU)'],
            ['VW', 'ID.3', '2019–2023', '77 kWh Pro S (EU)'],
            ['VW', 'ID.3', '2023–', '58 kWh Pro (EU)'],
            ['VW', 'ID.3', '2023–', '77 kWh Pro S / GTX (EU)'],
            ['VW', 'ID.3', '2021–', '45–62 kWh Smart Edition (CN)'],
            ['VW', 'ID.6 X', '2021–', '55 kWh Pure'],
            ['VW', 'ID.6 X', '2021–', '82 kWh Pro'],
            ['VW', 'ID.6 X', '2021–', '82 kWh Prime AWD'],
            ['VW', 'ID.6 Crozz', '2021–2026', '55 kWh Pure'],
            ['VW', 'ID.6 Crozz', '2021–2026', '82 kWh Pro'],
            ['VW', 'ID.6 Crozz', '2021–2026', '82 kWh Prime AWD'],
            ['VW', 'ID. UNYX 06', '2025–', '54 kWh Pure'],
            ['VW', 'ID. UNYX 06', '2025–', '80 kWh Pro / Ultra / Max'],
            ['VW', 'ID. UNYX 06', '2025–', '80 kWh Max AWD'],
            ['VW', 'ID.5', '2022–2023', '82 kWh Pro'],
            ['VW', 'ID.5', '2022–2023', '82 kWh GTX AWD'],
            ['VW', 'ID.5', '2024–', '82 kWh Pro / Pro S'],
            ['VW', 'ID.5', '2024–', '82 kWh GTX AWD'],
            ['VW', 'ID.7', '2023–', '77 kWh Pro'],
            ['VW', 'ID.7', '2023–', '86 kWh Pro S'],
            ['VW', 'ID.7', '2023–', '86 kWh GTX AWD'],
            ['VW', 'ID.7 Tourer', '2024–', '77 kWh Pro'],
            ['VW', 'ID.7 Tourer', '2024–', '86 kWh Pro S'],
            ['VW', 'ID.7 Tourer', '2024–', '86 kWh GTX AWD'],
            ['VW', 'ID.7 Vizzion', '2023–', '77–86 kWh'],

            ['Audi', 'Q4 e-tron', '2021–2023', '52 kWh 35 e-tron (EU)'],
            ['Audi', 'Q4 e-tron', '2021–2023', '82 kWh 40 e-tron (EU)'],
            ['Audi', 'Q4 e-tron', '2021–2023', '82 kWh 45 / 50 e-tron quattro (EU)'],
            ['Audi', 'Q4 e-tron', '2024–', '52 kWh 35 e-tron (EU)'],
            ['Audi', 'Q4 e-tron', '2024–', '82 kWh 40 e-tron (EU)'],
            ['Audi', 'Q4 e-tron', '2024–', '82 kWh 45 / 50 e-tron quattro (EU)'],
            ['Audi', 'Q4 e-tron', '2022–', '82 kWh 40 / 50 quattro (CN)'],
            ['Audi', 'Q4 Sportback e-tron', '2021–2023', '52 kWh 35 e-tron'],
            ['Audi', 'Q4 Sportback e-tron', '2021–2023', '82 kWh 40 / 45 / 50 e-tron'],
            ['Audi', 'Q4 Sportback e-tron', '2024–', '52 kWh 35 e-tron'],
            ['Audi', 'Q4 Sportback e-tron', '2024–', '82 kWh 40 / 50 e-tron'],
            ['Audi', 'Q5 e-tron', '2022–', '83 kWh 40 e-tron'],
            ['Audi', 'Q5 e-tron', '2022–', '83 kWh 50 e-tron quattro AWD'],

            ['BYD', 'Sealion 07 EV', '2024–', '72 kWh RWD (CN)'],
            ['BYD', 'Sealion 07 EV', '2024–', '81 kWh AWD (CN)'],
            ['BYD', 'Sealion 07 DM-i', '2025–', '27 kWh PHEV (CN)'],
            ['BYD', 'Song L EV', '2023–', '72 kWh RWD'],
            ['BYD', 'Song L EV', '2023–', '87 kWh RWD'],
            ['BYD', 'Song L EV', '2023–', '87 kWh AWD'],
            ['BYD', 'Song L DM-i', '2024–', '13 kWh PHEV'],
            ['BYD', 'Song L DM-i', '2024–', '18–27 kWh PHEV'],
            ['BYD', 'Seagull', '2023–', '30 kWh Vitality / Freedom'],
            ['BYD', 'Seagull', '2023–', '39 kWh Flying'],
            ['BYD', 'Sealion 06 EV', '2025–', '65 kWh RWD'],
            ['BYD', 'Sealion 06 EV', '2025–', '79 kWh RWD'],
            ['BYD', 'Sealion 06 EV', '2025–', '79 kWh AWD'],
            ['BYD', 'Sealion 06 DM-i', '2025–', '18–27 kWh PHEV'],
            ['BYD', 'Sealion 05 DM-i', '2024–2026', '18–27 kWh PHEV'],
            ['BYD', 'Sealion 05 DM-i', '2026–', '27–34 kWh PHEV'],
            ['BYD', 'Sealion 05 EV', '2024–2026', '50–61 kWh'],
            ['BYD', 'Sealion 05 EV', '2026–', '58–69 kWh'],
            ['BYD', 'Dolphin', '2021–2025', '31 kWh Standard Range'],
            ['BYD', 'Dolphin', '2021–2025', '45–50 kWh Extended / Performance'],
            ['BYD', 'Dolphin', '2025–', '45–50 kWh'],
            ['BYD', 'Yuan UP', '2024–', '32 kWh (CN)'],
            ['BYD', 'Yuan UP', '2024–', '45 kWh (CN)'],
            ['BYD', 'Yuan Plus', '2022–2026', '50 kWh'],
            ['BYD', 'Yuan Plus', '2022–2026', '60 kWh'],
            ['BYD', 'Yuan Plus', '2026–', '58–69 kWh'],
        ];
    }
}
