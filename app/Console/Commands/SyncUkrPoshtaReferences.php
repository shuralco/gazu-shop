<?php

namespace App\Console\Commands;

use App\Models\DisplaySetting;
use App\Models\UpCity;
use App\Models\UpPostOffice;
use App\Models\UpRegion;
use App\Services\UkrPoshtaApiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncUkrPoshtaReferences extends Command
{
    protected $signature = 'up:sync-references
                            {--regions : Sync only regions}
                            {--cities : Sync only cities}
                            {--post-offices : Sync only post offices for already-known cities}
                            {--all : Sync everything (default)}
                            {--limit-cities=0 : Limit cities sync to N regions (debugging)}';

    protected $description = 'Sync UkrPoshta address classifier data into local DB tables';

    public function handle(UkrPoshtaApiService $api): int
    {
        $regions = $this->option('regions');
        $cities = $this->option('cities');
        $postOffices = $this->option('post-offices');
        $all = $this->option('all') || (! $regions && ! $cities && ! $postOffices);

        if ($all || $regions) {
            $this->syncRegions($api);
        }

        if ($all || $cities) {
            $this->syncCities($api);
        }

        if ($all || $postOffices) {
            $this->syncPostOffices($api);
        }

        return self::SUCCESS;
    }

    protected function syncRegions(UkrPoshtaApiService $api): void
    {
        $this->info('Syncing regions…');
        $rows = $api->getRegions();
        $count = 0;
        foreach ($rows as $row) {
            $row = is_object($row) ? (array) $row : $row;
            $id = $row['REGION_ID'] ?? null;
            if (! $id) {
                continue;
            }
            UpRegion::updateOrCreate(
                ['id' => (int) $id],
                [
                    'name_ua' => $row['REGION_UA'] ?? '',
                    'name_en' => $row['REGION_EN'] ?? null,
                ]
            );
            $count++;
        }
        DisplaySetting::updateOrCreate(
            ['key' => 'up_regions_last_sync'],
            ['value' => now()->toDateTimeString(), 'group' => 'ukrposhta', 'type' => 'string', 'is_active' => true, 'title' => 'UP regions last sync']
        );
        $this->info("  → {$count} regions saved.");
    }

    protected function syncCities(UkrPoshtaApiService $api): void
    {
        $this->info('Syncing cities (per region)…');
        $regions = UpRegion::query()->orderBy('id')->get(['id', 'name_ua']);
        if ($regions->isEmpty()) {
            $this->warn('  no regions found in DB — run --regions first');

            return;
        }

        $limit = (int) $this->option('limit-cities');
        if ($limit > 0) {
            $regions = $regions->take($limit);
            $this->warn("  --limit-cities={$limit} active");
        }

        $total = 0;
        foreach ($regions as $region) {
            $rows = $api->getCities(null, null, (int) $region->id);
            $batch = 0;
            foreach ($rows as $row) {
                $row = is_object($row) ? (array) $row : $row;
                $cityId = $row['CITY_ID'] ?? null;
                if (! $cityId) {
                    continue;
                }
                UpCity::updateOrCreate(
                    ['id' => (int) $cityId],
                    [
                        'region_id' => (int) $region->id,
                        'district_id' => isset($row['DISTRICT_ID']) ? (int) $row['DISTRICT_ID'] : null,
                        'name_ua' => $row['CITY_UA'] ?? '',
                        'name_en' => $row['CITY_EN'] ?? null,
                        'district_ua' => $row['DISTRICT_UA'] ?? null,
                        'city_type_ua' => $row['SHORTCITYTYPE_UA'] ?? null,
                        'population' => isset($row['POPULATION']) ? (int) $row['POPULATION'] : null,
                        'postcode' => $row['POSTCODE'] ?? null,
                    ]
                );
                $batch++;
                $total++;
            }
            $this->line("  region {$region->id} ({$region->name_ua}): {$batch} cities");
        }
        DisplaySetting::updateOrCreate(
            ['key' => 'up_cities_last_sync'],
            ['value' => now()->toDateTimeString(), 'group' => 'ukrposhta', 'type' => 'string', 'is_active' => true, 'title' => 'UP cities last sync']
        );
        $this->info("Cities total: {$total}");
    }

    protected function syncPostOffices(UkrPoshtaApiService $api): void
    {
        $this->info('Syncing post offices for cities with population>=10000…');
        $cities = UpCity::query()
            ->where('population', '>=', 10000)
            ->orWhere('id', '<=', 100) // initial batch
            ->orderByDesc('population')
            ->limit(500)
            ->get(['id', 'name_ua']);

        if ($cities->isEmpty()) {
            $this->warn('  no cities found — run --cities first');

            return;
        }

        $total = 0;
        foreach ($cities as $city) {
            $rows = $api->getPostOffices((int) $city->id);
            $batch = 0;
            foreach ($rows as $row) {
                $row = is_object($row) ? (array) $row : $row;
                $poId = $row['ID'] ?? $row['id'] ?? null;
                if (! $poId) {
                    continue;
                }
                UpPostOffice::updateOrCreate(
                    ['po_id' => (int) $poId],
                    [
                        'city_id' => (int) $city->id,
                        'postcode' => $row['POSTCODE'] ?? '',
                        'city_ua' => $row['PDCITY_UA'] ?? $city->name_ua,
                        'district_ua' => $row['DISTRICT_UA'] ?? null,
                        'region_ua' => $row['REGION_UA'] ?? null,
                        'type_acronym' => $row['TYPE_ACRONYM'] ?? null,
                        'type_long' => $row['TYPE_LONG'] ?? null,
                        'address' => $row['ADDRESS'] ?? null,
                        'lock_code' => $row['LOCK_CODE'] ?? null,
                        'is_active' => empty($row['LOCK_CODE']) || in_array($row['LOCK_CODE'], ['', '00']),
                    ]
                );
                $batch++;
                $total++;
            }
            if ($batch > 0) {
                $this->line("  city {$city->id} ({$city->name_ua}): {$batch} POs");
            }
        }
        DisplaySetting::updateOrCreate(
            ['key' => 'up_post_offices_last_sync'],
            ['value' => now()->toDateTimeString(), 'group' => 'ukrposhta', 'type' => 'string', 'is_active' => true, 'title' => 'UP post offices last sync']
        );
        $this->info("Post offices total: {$total}");
    }
}
