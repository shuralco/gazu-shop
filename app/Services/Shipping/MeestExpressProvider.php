<?php

namespace App\Services\Shipping;

use App\Services\DelengineProvider;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use MeestExpress\MeestExpress;

class MeestExpressProvider
{
    private ?MeestExpress $client = null;

    private DelengineProvider $delengine;

    private string $login;

    private string $password;

    public function __construct()
    {
        $this->login = config('meest.login') ?? '';
        $this->password = config('meest.password') ?? '';
        $this->delengine = new DelengineProvider;
    }

    private function getClient(): ?MeestExpress
    {
        if ($this->client === null && ! empty($this->login) && ! empty($this->password)) {
            try {
                $this->client = new MeestExpress($this->login, $this->password);
            } catch (\Exception $e) {
                Log::warning('Failed to create Meest Express client: '.$e->getMessage());

                return null;
            }
        }

        return $this->client;
    }

    private function ensureAuthenticated(): bool
    {
        $cacheKey = 'meest_authenticated';

        if (Cache::has($cacheKey)) {
            return true;
        }

        $client = $this->getClient();
        if (! $client) {
            Log::info('Meest Express client not available');

            return false;
        }

        try {
            $result = $client->authorize();

            if ($result && $client->getStatus() === 'ok') {
                Cache::put($cacheKey, true, now()->addHours(23));
                Log::info('Meest Express authentication successful');

                return true;
            }

            Log::warning('Meest Express authentication failed');

            return false;

        } catch (\Exception $e) {
            Log::warning('Meest Express authentication exception: '.$e->getMessage());

            return false;
        }
    }

    public function getCities(?string $search = null): Collection
    {
        // Use Delengine API for real Ukrainian cities data
        try {
            $cities = $this->delengine->getCities($search);

            if ($cities->count() > 0) {
                Log::info('Meest Express using Delengine API for cities');

                return $cities;
            }

            Log::warning('Delengine API returned no cities, falling back to demo data');

        } catch (\Exception $e) {
            Log::error('Delengine cities error: '.$e->getMessage().', using demo data');
        }

        // Fallback to demo data if Delengine fails
        return $this->getDemoCities($search);
    }

    public function getBranches(?string $cityId = null): Collection
    {
        // Use Delengine API for real Meest Express branches data
        try {
            $branches = $this->delengine->getBranches($cityId, 'meest');

            if ($branches->count() > 0) {
                Log::info('Meest Express using Delengine API for branches');

                return $branches;
            }

            Log::warning('Delengine API returned no Meest branches, falling back to demo data');

        } catch (\Exception $e) {
            Log::error('Delengine branches error: '.$e->getMessage().', using demo data');
        }

        // Fallback to demo data if Delengine fails
        return $this->getDemoBranches($cityId);
    }

    public function calculateShippingCost($order, array $destination): float
    {
        if (! $this->ensureAuthenticated()) {
            return 55.0; // fallback cost
        }

        try {
            $result = $this->client->calculate([
                'city_sender' => config('meest.sender_city_id', 1),
                'city_recipient' => $destination['city_id'] ?? null,
                'weight' => $this->calculateWeight($order),
                'length' => 20,
                'width' => 15,
                'height' => 10,
                'declared_cost' => $order->total ?? 0,
            ]);

            if ($this->client->getStatus() === 'ok') {
                $calculation = $this->client->getResult();

                return (float) ($calculation['cost'] ?? 55.0);
            }

            return 55.0; // fallback cost

        } catch (\Exception $e) {
            Log::error('Meest Express shipping calculation error: '.$e->getMessage());

            return 55.0;
        }
    }

    private function calculateWeight($order): float
    {
        if (method_exists($order, 'orderProducts')) {
            return $order->orderProducts->sum(function ($item) {
                return ($item->product->weight ?? 0.5) * $item->quantity;
            });
        }

        return 1.0;
    }

    /**
     * Спроба отримати міста через прямі HTTP запити без автентифікації
     */
    private function getPublicCities(?string $search = null): Collection
    {
        try {
            $baseUrl = 'https://api.meest.com/v3.0/openAPI';

            $params = [
                'method' => 'search/city',
                'format' => 'json',
            ];

            if ($search) {
                $params['name'] = $search.'%';
            }

            // Спробувати без country_id
            $response = Http::connectTimeout(5)->timeout(10)->get($baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Meest Express public cities call successful', ['data' => $data]);

                if (isset($data['result']) && is_array($data['result'])) {
                    return collect($data['result'])->map(function ($city) {
                        return [
                            'id' => $city['cityIDOR'] ?? $city['id'] ?? null,
                            'name' => $city['cityDescr'] ?? $city['name'] ?? 'Unknown',
                            'region' => $city['regionDescr'] ?? $city['region'] ?? null,
                        ];
                    });
                }
            }

            // Спробувати з country_id для України
            $params['country_id'] = 'c35b6195-4ea3-11de-8591-001d600938f8';
            $response = Http::connectTimeout(5)->timeout(10)->get($baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Meest Express public cities with country_id successful', ['data' => $data]);

                if (isset($data['result']) && is_array($data['result'])) {
                    return collect($data['result'])->map(function ($city) {
                        return [
                            'id' => $city['cityIDOR'] ?? $city['id'] ?? null,
                            'name' => $city['cityDescr'] ?? $city['name'] ?? 'Unknown',
                            'region' => $city['regionDescr'] ?? $city['region'] ?? null,
                        ];
                    });
                }
            }

            Log::info('Meest Express public cities failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Meest Express public cities error: '.$e->getMessage());
        }

        return collect([]);
    }

    /**
     * Спроба отримати відділення через прямі HTTP запити без автентифікації
     */
    private function getPublicBranches(?string $cityId = null): Collection
    {
        try {
            $baseUrl = 'https://api.meest.com/v3.0/openAPI';

            $params = [
                'method' => 'search/branch',
                'format' => 'json',
            ];

            if ($cityId) {
                $params['city_id'] = $cityId;
            }

            $response = Http::connectTimeout(5)->timeout(10)->get($baseUrl, $params);

            if ($response->successful()) {
                $data = $response->json();
                Log::info('Meest Express public branches call successful', ['data' => $data]);

                if (isset($data['result']) && is_array($data['result'])) {
                    return collect($data['result'])->map(function ($branch) {
                        return [
                            'id' => $branch['branchIDOR'] ?? $branch['id'] ?? null,
                            'name' => $branch['branchDescr'] ?? $branch['name'] ?? 'Відділення',
                            'address' => $branch['addressDescr'] ?? $branch['address'] ?? '',
                            'phone' => $branch['phone'] ?? null,
                        ];
                    });
                }
            }

            Log::info('Meest Express public branches failed', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

        } catch (\Exception $e) {
            Log::warning('Meest Express public branches error: '.$e->getMessage());
        }

        return collect([]);
    }

    /**
     * Демо міста для fallback
     */
    private function getDemoCities(?string $search = null): Collection
    {
        $cities = collect([
            ['id' => 'demo-kyiv', 'name' => 'Київ', 'region' => 'Київська область'],
            ['id' => 'demo-lviv', 'name' => 'Львів', 'region' => 'Львівська область'],
            ['id' => 'demo-dnipro', 'name' => 'Дніпро', 'region' => 'Дніпропетровська область'],
            ['id' => 'demo-odesa', 'name' => 'Одеса', 'region' => 'Одеська область'],
            ['id' => 'demo-krasyliv', 'name' => 'Красилів', 'region' => 'Хмельницька область'],
        ]);

        if ($search) {
            return $cities->filter(function ($city) use ($search) {
                return stripos($city['name'], $search) !== false;
            });
        }

        return $cities;
    }

    /**
     * Демо відділення для fallback
     */
    private function getDemoBranches(?string $cityId = null): Collection
    {
        $branches = collect([
            ['id' => 'demo-branch-1', 'name' => 'Відділення №1 - вул. Хрещатик, 1', 'address' => 'вул. Хрещатик, 1', 'phone' => '+380441234567'],
            ['id' => 'demo-branch-2', 'name' => 'Відділення №2 - вул. Саксаганського, 10', 'address' => 'вул. Саксаганського, 10', 'phone' => '+380441234568'],
            ['id' => 'demo-branch-3', 'name' => 'Відділення №3 - просп. Перемоги, 25', 'address' => 'просп. Перемоги, 25', 'phone' => '+380441234569'],
        ]);

        if ($cityId && $cityId === 'demo-krasyliv') {
            return collect([
                ['id' => 'demo-krasyliv-1', 'name' => 'Відділення №1 - центр міста', 'address' => 'вул. Соборна, 15', 'phone' => '+380383912345'],
                ['id' => 'demo-krasyliv-2', 'name' => 'Відділення №2 - район автовокзалу', 'address' => 'вул. Вокзальна, 8', 'phone' => '+380383912346'],
            ]);
        }

        return $branches;
    }
}
