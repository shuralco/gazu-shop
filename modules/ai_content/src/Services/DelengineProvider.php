<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DelengineProvider
{
    private string $apiKey;

    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('delengine.api_key') ?? 'v4n208uaysugpqe6v3ijelusl601fduv';
        $this->baseUrl = config('delengine.base_url') ?? 'https://api.delengine.com/v1.0';
    }

    /**
     * Отримати міста України
     */
    public function getCities(?string $search = null): Collection
    {
        $cacheKey = 'delengine_cities_'.md5($search ?? 'all');

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $params = ['token' => $this->apiKey];
            if ($search) {
                $params['search'] = $search;
            }

            $response = Http::connectTimeout(5)->timeout(15)->get($this->baseUrl.'/settlements', $params);

            if ($response->successful()) {
                $data = $response->json();

                $cities = collect($data['data'] ?? [])->map(function ($settlement) {
                    return [
                        'id' => $settlement['uuid'],
                        'name' => $settlement['name_uk'] ?? $settlement['name_ru'] ?? 'Unknown',
                        'region' => $settlement['region_name_uk'] ?? $settlement['region_name_ru'] ?? null,
                        'district' => $settlement['district_name_uk'] ?? $settlement['district_name_ru'] ?? null,
                        'type' => $settlement['type_uk'] ?? $settlement['type_ru'] ?? null,
                    ];
                });

                Cache::put($cacheKey, $cities, now()->addHours(24));

                return $cities;
            }

            Log::warning('Delengine cities API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('Delengine cities error: '.$e->getMessage());
        }

        return collect([]);
    }

    /**
     * Отримати відділення конкретної служби доставки для міста
     */
    public function getBranches(string $cityUuid, string $companyCode = 'meest'): Collection
    {
        $cacheKey = 'delengine_branches_'.$companyCode.'_'.md5($cityUuid);

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $companyUuid = config('delengine.companies.'.$companyCode);

            $response = Http::connectTimeout(5)->timeout(15)->get($this->baseUrl.'/departments', [
                'token' => $this->apiKey,
                'settlement_uuid' => $cityUuid,
                'company_uuid' => $companyUuid,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $branches = collect($data['data'] ?? [])->map(function ($branch) {
                    return [
                        'id' => $branch['uuid'],
                        'name' => $this->formatBranchName($branch),
                        'address' => $branch['address_uk'] ?? $branch['address_ru'] ?? '',
                        'phone' => $branch['phone'] ?? null,
                        'working_hours' => $branch['schedules'] ?? null,
                        'status' => $branch['status'] ?? 1,
                        'number' => $branch['number'] ?? null,
                    ];
                });

                Cache::put($cacheKey, $branches, now()->addHours(24));

                return $branches;
            }

            Log::warning('Delengine branches API failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::error('Delengine branches error: '.$e->getMessage());
        }

        return collect([]);
    }

    /**
     * Форматувати назву відділення
     */
    private function formatBranchName(array $branch): string
    {
        $number = $branch['number'] ?? null;
        $name = $branch['name_uk'] ?? $branch['name_ru'] ?? null;

        if ($number && $name) {
            return "Відділення №{$number} - {$name}";
        } elseif ($number) {
            return "Відділення №{$number}";
        } elseif ($name) {
            return $name;
        }

        return 'Відділення';
    }

    /**
     * Отримати список служб доставки
     */
    public function getCompanies(): Collection
    {
        $cacheKey = 'delengine_companies';

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            $response = Http::connectTimeout(5)->timeout(15)->get($this->baseUrl.'/companies', [
                'token' => $this->apiKey,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                $companies = collect($data['data'] ?? [])->map(function ($company) {
                    return [
                        'uuid' => $company['uuid'],
                        'code' => $company['code'],
                        'name' => $company['name_uk'] ?? $company['name_ru'] ?? 'Unknown',
                    ];
                });

                Cache::put($cacheKey, $companies, now()->addHours(24));

                return $companies;
            }

        } catch (\Exception $e) {
            Log::error('Delengine companies error: '.$e->getMessage());
        }

        return collect([]);
    }
}
