<?php

namespace App\Console\Commands;

use App\Models\NpArea;
use App\Models\NpCity;
use App\Models\NpWarehouse;
use App\Services\NovaPoshtaApiService;
use Illuminate\Console\Command;

class NovaPoshtaSync extends Command
{
    protected $signature = 'np:sync
        {--areas-only : Синхронізувати тільки області}
        {--cities-only : Синхронізувати тільки міста}
        {--warehouses-only : Синхронізувати тільки відділення}
        {--city= : Ref міста для синхронізації відділень}';

    protected $description = 'Синхронізація довідників Нової Пошти (області, міста, відділення)';

    public function handle(NovaPoshtaApiService $api): int
    {
        $areasOnly = $this->option('areas-only');
        $citiesOnly = $this->option('cities-only');
        $warehousesOnly = $this->option('warehouses-only');
        $specificCity = $this->option('city');
        $syncAll = ! $areasOnly && ! $citiesOnly && ! $warehousesOnly && ! $specificCity;

        if ($syncAll || $areasOnly) {
            $this->syncAreas($api);
        }

        if ($syncAll || $citiesOnly) {
            $this->syncCities($api);
        }

        if ($syncAll || $warehousesOnly) {
            $this->syncWarehouses($api);
        }

        if ($specificCity) {
            $this->syncWarehousesForCity($api, $specificCity);
        }

        $this->newLine();
        $this->info('Синхронізацію завершено.');

        return self::SUCCESS;
    }

    /**
     * Синхронізація областей
     */
    private function syncAreas(NovaPoshtaApiService $api): void
    {
        $this->info('Синхронізація областей...');

        $response = $api->getAreas();

        if (! ($response['success'] ?? false) || empty($response['data'])) {
            $this->error('Не вдалося отримати області: '.implode(', ', $response['errors'] ?? ['Unknown error']));

            return;
        }

        $areas = $response['data'];
        $bar = $this->output->createProgressBar(count($areas));
        $bar->start();

        $synced = 0;
        foreach ($areas as $area) {
            NpArea::updateOrCreate(
                ['ref' => $area['Ref']],
                ['description' => $area['Description']]
            );
            $synced++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Синхронізовано {$synced} областей.");
    }

    /**
     * Синхронізація міст (посторінково)
     */
    private function syncCities(NovaPoshtaApiService $api): void
    {
        $this->info('Синхронізація міст...');

        $page = 1;
        $totalSynced = 0;

        // Спочатку дізнаємось загальну кількість
        $firstResponse = $api->getCities('', 1, 150);

        if (! ($firstResponse['success'] ?? false)) {
            $this->error('Не вдалося отримати міста: '.implode(', ', $firstResponse['errors'] ?? ['Unknown error']));

            return;
        }

        $totalCount = $firstResponse['info']['totalCount'] ?? count($firstResponse['data']);
        $bar = $this->output->createProgressBar($totalCount);
        $bar->start();

        // Обробимо першу сторінку
        $totalSynced += $this->processCitiesPage($firstResponse['data'], $bar);

        // Продовжуємо по сторінках
        $page = 2;
        while (true) {
            $response = $api->getCities('', $page, 150);

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                break;
            }

            $totalSynced += $this->processCitiesPage($response['data'], $bar);
            $page++;
        }

        $bar->finish();
        $this->newLine();
        $this->info("Синхронізовано {$totalSynced} міст.");
    }

    /**
     * Обробити сторінку міст
     */
    private function processCitiesPage(array $cities, $bar): int
    {
        $count = 0;
        foreach ($cities as $city) {
            NpCity::updateOrCreate(
                ['ref' => $city['Ref']],
                [
                    'description' => $city['Description'],
                    'description_ru' => $city['DescriptionRu'] ?? null,
                    'area_ref' => $city['Area'] ?? null,
                    'area_description' => $city['AreaDescription'] ?? null,
                    'settlement_type' => $city['SettlementTypeDescription'] ?? null,
                    'is_branch' => (bool) ($city['IsBranch'] ?? false),
                ]
            );
            $count++;
            $bar->advance();
        }

        return $count;
    }

    /**
     * Синхронізація всіх відділень (по містах з бази)
     */
    private function syncWarehouses(NovaPoshtaApiService $api): void
    {
        $this->info('Синхронізація відділень...');

        $cities = NpCity::pluck('ref')->toArray();

        if (empty($cities)) {
            $this->warn('Спочатку синхронізуйте міста: php artisan np:sync --cities-only');

            return;
        }

        $bar = $this->output->createProgressBar(count($cities));
        $bar->start();

        $totalSynced = 0;
        foreach ($cities as $cityRef) {
            $totalSynced += $this->syncWarehousesForCity($api, $cityRef, false);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Синхронізовано {$totalSynced} відділень.");
    }

    /**
     * Синхронізація відділень для конкретного міста
     */
    private function syncWarehousesForCity(NovaPoshtaApiService $api, string $cityRef, bool $showInfo = true): int
    {
        if ($showInfo) {
            $city = NpCity::where('ref', $cityRef)->first();
            $this->info('Синхронізація відділень для '.($city->description ?? $cityRef).'...');
        }

        $page = 1;
        $totalSynced = 0;

        while (true) {
            $response = $api->getWarehouses($cityRef, '', 500, $page);

            if (! ($response['success'] ?? false) || empty($response['data'])) {
                break;
            }

            foreach ($response['data'] as $warehouse) {
                NpWarehouse::updateOrCreate(
                    ['ref' => $warehouse['Ref']],
                    [
                        'site_key' => $warehouse['Number'] ?? null,
                        'number' => $warehouse['Number'] ?? null,
                        'description' => $warehouse['Description'],
                        'short_address' => $warehouse['ShortAddress'] ?? null,
                        'city_ref' => $warehouse['CityRef'] ?? $cityRef,
                        'city_description' => $warehouse['CityDescription'] ?? null,
                        'type_ref' => $warehouse['TypeOfWarehouse'] ?? null,
                        'type_description' => $warehouse['CategoryOfWarehouse'] ?? null,
                        'longitude' => $warehouse['Longitude'] ?? null,
                        'latitude' => $warehouse['Latitude'] ?? null,
                        'total_max_weight' => (int) ($warehouse['TotalMaxWeightAllowed'] ?? 30),
                        'max_dimensions' => $warehouse['PlaceMaxWeightAllowed'] ?? null,
                        'is_active' => ($warehouse['WarehouseStatus'] ?? 'Working') === 'Working',
                    ]
                );
                $totalSynced++;
            }

            // Якщо отримали менше ліміту — це остання сторінка
            if (count($response['data']) < 500) {
                break;
            }

            $page++;
        }

        if ($showInfo) {
            $this->info("Синхронізовано {$totalSynced} відділень.");
        }

        return $totalSynced;
    }
}
